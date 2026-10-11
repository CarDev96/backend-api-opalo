<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Models\EvaluationType;
use App\Models\Examinee;
use App\Models\PsychologistSchedule;
use App\Models\User;
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

    /**
     * Obtiene el catálogo de tipos de evaluación activos
     */
    public function getActiveEvaluationTypes(): Collection
    {
        return EvaluationType::where('is_active', true)->orderBy('modality')->orderBy('name')->get();
    }

    /**
     * Obtiene los psicólogos activos que tienen al menos un día configurado en su agenda
     */
    public function getAvailablePsychologists(): Collection
    {
		return User::whereHas('role', fn($q) => $q->where('name', 'psychology'))
				->with(['psychologistSchedules' => fn($q) => $q->where('is_active', true)])
				->whereNull('client_id')
				->orderBy('name')
				->get();
    }

    /**
     * Calcula los bloques de horarios disponibles (ej. 08:00, 09:00...) para un psicólogo en una fecha
     */
    public function getAvailableSlots(string $psychologistId, string $dateString): array
    {
        $date = Carbon::parse($dateString);
        // Carbon: 1 = Lunes, ..., 6 = Sábado, 7 = Domingo
        $dayOfWeek = $date->dayOfWeekIso; // 1 a 7

        if ($dayOfWeek > 6) {
            return []; // Domingo cerrado
        }

        // Buscar horario configurado para ese día de la semana
        $schedule = PsychologistSchedule::where('user_id', $psychologistId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return []; // El psicólogo no labora ese día
        }

        $startHour = (int) substr($schedule->start_time, 0, 2);
        $endHour   = (int) substr($schedule->end_time, 0, 2);
        $slotMinutes = $schedule->slot_duration_minutes ?: 60;

        // Citas ya existentes para ese psicólogo en esa fecha
        $bookedAppointments = Appointment::where('psychologist_id', $psychologistId)
            ->where('date', $dateString)
            ->whereIn('status', ['scheduled', 'attended'])
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        $slots = [];
        $current = Carbon::createFromTime($startHour, 0);
        $endLimit = Carbon::createFromTime($endHour, 0);

        while ($current->copy()->addMinutes($slotMinutes)->lte($endLimit)) {
            $timeStr = $current->format('H:i');
            $endTimeStr = $current->copy()->addMinutes($slotMinutes)->format('H:i');

            // Si es hoy, no permitir horas que ya pasaron
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
     * Agendar la cita
     */
    public function bookAppointment(string $clientId, array $data): Appointment
    {
        $evaluationType = EvaluationType::findOrFail($data['evaluation_type_id']);
        $startTime = $data['start_time'];
        $endTime = Carbon::createFromFormat('H:i', $startTime)->addMinutes(60)->format('H:i');

        // Validar que el evaluado pertenezca a la empresa cliente
        $examinee = Examinee::where('client_id', $clientId)->findOrFail($data['examinee_id']);

        // Validar colisión de horario
        $exists = Appointment::where('psychologist_id', $data['psychologist_id'])
            ->where('date', $data['date'])
            ->where('start_time', $startTime)
            ->whereIn('status', ['scheduled', 'attended'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_time' => ['Este horario acaba de ser reservado. Por favor selecciona otro.'],
            ]);
        }

        return DB::transaction(function () use ($clientId, $examinee, $evaluationType, $data, $startTime, $endTime) {
            $appointment = Appointment::create([
                'client_id'          => $clientId,
                'examinee_id'        => $examinee->id,
                'psychologist_id'    => $data['psychologist_id'],
                'evaluation_type_id' => $evaluationType->id,
                'date'               => $data['date'],
                'start_time'         => $startTime,
                'end_time'           => $endTime,
                'modality'           => $evaluationType->modality,
                'status'             => 'scheduled',
                'notes'              => $data['notes'] ?? null,
            ]);

            // Actualizar evaluado a scheduled
            $examinee->update(['status' => 'scheduled']);

            // Cargar relaciones para el correo
            $appointment->load(['client', 'examinee', 'psychologist', 'evaluationType']);

            // Despachar Job de correo
            $this->sendEmailService->notifyAppointmentBooked($appointment);

            return $appointment;
        });
    }

    /**
     * Citas del cliente autenticado
     */
    public function getClientAppointments(string $clientId): Collection
    {
        return Appointment::with(['examinee', 'psychologist', 'evaluationType'])
            ->where('client_id', $clientId)
            ->latest('date')
            ->get();
    }
}
