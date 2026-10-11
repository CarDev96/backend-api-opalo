<?php

namespace App\Repositories;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ClinicalAppointmentRepository
{
    /**
     * Consulta las citas asignadas al especialista aplicando filtros de tiempo
     */
    public function getAppointmentsBySpecialist(string $specialistId, string $timeframe = 'today'): Collection
    {
        $query = Appointment::with([
                'client',
                'examinee',
                'evaluationType',
            ])
            ->where('psychologist_id', $specialistId);

        if ($timeframe === 'today') {
            $query->whereDate('date', Carbon::today());
        } elseif ($timeframe === 'week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek   = Carbon::now()->endOfWeek()->toDateString();
            $query->whereBetween('date', [$startOfWeek, $endOfWeek]);
        }

        return $query->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Actualiza el estatus y las observaciones de la sesión
     */
    public function updateSession(string $specialistId, string $appointmentId, array $payload): Appointment
    {
        $appointment = Appointment::where('psychologist_id', $specialistId)
            ->findOrFail($appointmentId);

        $appointment->update([
            'status' => $payload['status'],
            'notes'  => $payload['notes'] ?? $appointment->notes,
        ]);

        if ($payload['status'] === 'attended' && $appointment->examinee) {
            $appointment->examinee->update(['status' => 'completed']);
        }

        return $appointment->fresh(['client', 'examinee', 'evaluationType']);
    }

    /**
     * Obtiene métricas agregadas y citas prioritarias para el Dashboard (/home) del especialista
     */
    public function getDashboardMetrics(string $specialistId): array
    {
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek   = Carbon::now()->endOfWeek()->toDateString();

        $baseQuery = Appointment::where('psychologist_id', $specialistId);

        // Métricas de conteo
        $todayTotal     = (clone $baseQuery)->whereDate('date', $today)->count();
        $todayPending   = (clone $baseQuery)->whereDate('date', $today)->where('status', 'scheduled')->count();
        $weekTotal      = (clone $baseQuery)->whereBetween('date', [$startOfWeek, $endOfWeek])->count();
        $totalAttended  = (clone $baseQuery)->where('status', 'attended')->count();
        $totalCancelled = (clone $baseQuery)->whereIn('status', ['cancelled', 'no_show'])->count();

        // Candidatos únicos atendidos o asignados
        $totalExaminees = (clone $baseQuery)->distinct('examinee_id')->count('examinee_id');

        // Próxima cita de hoy pendiente por atender
        $nextAppointment = (clone $baseQuery)
            ->with(['client', 'examinee', 'evaluationType'])
            ->whereDate('date', $today)
            ->where('status', 'scheduled')
            ->orderBy('start_time', 'asc')
            ->first();

        // Lista compacta de citas del día
        $todayAppointments = (clone $baseQuery)
            ->with(['client', 'examinee', 'evaluationType'])
            ->whereDate('date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        return [
            'metrics' => [
                'today_total'     => $todayTotal,
                'today_pending'   => $todayPending,
                'week_total'      => $weekTotal,
                'total_attended'  => $totalAttended,
                'total_cancelled' => $totalCancelled,
                'total_examinees' => $totalExaminees,
            ],
            'next_appointment'   => $nextAppointment,
            'today_appointments' => $todayAppointments,
        ];
    }
}
