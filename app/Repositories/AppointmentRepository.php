<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Models\EvaluationType;
use App\Models\Examinee;
use App\Models\User;
use App\Models\PsychologistSchedule;
use App\Services\SendEmailService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentRepository
{
    public function __construct(
        protected SendEmailService $sendEmailService
    ) {}

    public function getActiveEvaluationTypes(): Collection
    {
        return EvaluationType::where('is_active', true)->orderBy('modality')->orderBy('name')->get();
    }

    /**
     * Trae a los psicólogos forzando el mapeo de los horarios para que Vue
     * pueda leerlos y habilite los días en el calendario sin problemas.
     */
    public function getAvailablePsychologists(): \Illuminate\Support\Collection
    {
        $psychologists = User::whereHas('role', fn($q) => $q->where('name', 'psychology'))
            ->with(['psychologistSchedules' => function ($q) {
                $q->where('is_active', true);
            }])
            ->whereHas('psychologistSchedules', function ($q) {
                $q->where('is_active', true);
            })
            ->whereNull('client_id')
            ->orderBy('name')
            ->get();

        // Mapeo blindado: Construimos el array exacto que necesita Vue
        return $psychologists->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'psychologist_schedules' => $p->psychologistSchedules->map(function ($schedule) {
                    return [
                        'day_of_week' => $schedule->day_of_week,
                        'start_time'  => $schedule->start_time,
                        'end_time'    => $schedule->end_time,
                        'is_active'   => $schedule->is_active,
                    ];
                })->toArray(),
            ];
        });
    }

    /**
     * Calcula los turnos de 30 minutos
     */
    public function getAvailableSlots(string $psychologistId, string $dateString): array
    {
        $date = Carbon::parse($dateString);
        $dayOfWeek = $date->dayOfWeekIso;

        if ($dayOfWeek > 6) {
            return []; // Domingo cerrado
        }

        $schedule = PsychologistSchedule::where('user_id', $psychologistId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule || !$schedule->start_time || !$schedule->end_time) {
            return [];
        }

        $slotMinutes = 30;

        $bookedAppointments = Appointment::where('psychologist_id', $psychologistId)
            ->where('date', $dateString)
            ->whereIn('status', ['scheduled', 'attended'])
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        $slots = [];

        [$startH, $startM] = explode(':', substr($schedule->start_time, 0, 5));
        [$endH, $endM]     = explode(':', substr($schedule->end_time, 0, 5));

        $current  = Carbon::createFromTime((int)$startH, (int)$startM);
        $endLimit = Carbon::createFromTime((int)$endH, (int)$endM);

        while ($current->copy()->addMinutes($slotMinutes)->lte($endLimit)) {
            $timeStr    = $current->format('H:i');
            $endTimeStr = $current->copy()->addMinutes($slotMinutes)->format('H:i');

            $isPastTime = $date->isToday() && $current->lte(now());

            if (!in_array($timeStr, $bookedAppointments) && !$isPastTime) {
                $slots[] = [
                    'start_time' => $timeStr,
                    'end_time'   => $endTimeStr,
                    'label'      => Carbon::createFromFormat('H:i', $timeStr)->format('h:i A') . ' - ' . Carbon::createFromFormat('H:i', $endTimeStr)->format('h:i A'),
                ];
            }

            $current->addMinutes($slotMinutes);
        }

        return $slots;
    }

    /**
     * Agendar y bloquear 30 minutos
     */
    public function bookAppointment(array $data): Appointment
    {
        $evaluationType = EvaluationType::findOrFail($data['evaluation_type_id']);
        $startTime = substr($data['start_time'], 0, 5);
        $endTime = Carbon::createFromFormat('H:i', $startTime)->addMinutes(30)->format('H:i');

        $examinee = Examinee::where('client_id', $data['client_id'])->findOrFail($data['examinee_id']);

        $exists = Appointment::where('psychologist_id', $data['psychologist_id'])
            ->where('date', $data['date'])
            ->where('start_time', $startTime)
            ->whereIn('status', ['scheduled', 'attended'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_time' => ['Este horario ya ha sido reservado.'],
            ]);
        }

        return DB::transaction(function () use ($examinee, $evaluationType, $data, $startTime, $endTime) {
            $appointment = Appointment::create([
                'client_id'          => $data['client_id'],
                'examinee_id'        => $examinee->id,
                'psychologist_id'    => $data['psychologist_id'],
                'evaluation_type_id' => $evaluationType->id,
                'date'               => $data['date'],
                'start_time'         => $startTime,
                'end_time'           => $endTime,
                'modality'           => $evaluationType->modality ?? 'presencial',
                'status'             => 'scheduled',
                'notes'              => $data['notes'] ?? null,
            ]);

            $examinee->update(['status' => 'scheduled']);
            $appointment->load(['client', 'examinee', 'psychologist', 'evaluationType']);

            $this->sendEmailService->notifyAppointmentBooked($appointment);

            return $appointment;
        });
    }

    public function getClientAppointments(string $clientId): Collection
    {
        return Appointment::with(['examinee', 'psychologist', 'evaluationType'])
            ->where('client_id', $clientId)
            ->latest('date')
            ->get();
    }

/**
     * Obtiene los clientes activos con evaluados elegibles para agendar cita.
     */
    public function getClientsWithEligibleExaminees()
    {
        // Si el modelo Client tiene la relación examinees o candidates:
        return \App\Models\Client::where('is_active', true)
            ->when(method_exists(\App\Models\Client::class, 'examinees'), function ($query) {
                $query->with(['examinees' => function ($q) {
                    // Filtrar si tienes un estatus tipo pendiente o sin cita
                    if (\Illuminate\Support\Facades\Schema::hasColumn('examinees', 'status')) {
                        $q->whereIn('status', ['pending', 'registered', 'sin_cita']);
                    }
                }]);
            }, function ($query) {
                // Fallback si la relación se llama candidates
                if (method_exists(\App\Models\Client::class, 'candidates')) {
                    $query->with('candidates');
                }
            })
            ->orderBy('company_name', 'asc')
            ->get();
    }
}
