<?php

namespace App\Repositories;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PsychologistAppointmentRepository
{
    /**
     * Retrieve appointments assigned to the authenticated psychologist.
     */
    public function getAppointmentsByPsychologist(string $psychologistId, string $scope = 'today'): Collection
    {
        $query = Appointment::with([
            'examinee',
            'client',
            'evaluationType',
        ])
        ->where('psychologist_id', $psychologistId);

        if ($scope === 'today') {
            $query->whereDate('date', Carbon::today());
        } elseif ($scope === 'week') {
            $query->whereBetween('date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        }

        return $query->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Update status and operational notes of an appointment.
     */
    public function updateStatus(string $psychologistId, string $appointmentId, string $status, ?string $notes = null): Appointment
    {
        $appointment = Appointment::where('psychologist_id', $psychologistId)
            ->findOrFail($appointmentId);

        $payload = ['status' => $status];
        if ($notes !== null) {
            $payload['notes'] = $notes;
        }

        $appointment->update($payload);

        // Synchronize examinee status if attended
        if ($status === 'attended') {
            $appointment->examinee?->update(['status' => 'completed']);
        }

        return $appointment->fresh(['examinee', 'client', 'evaluationType']);
    }
}
