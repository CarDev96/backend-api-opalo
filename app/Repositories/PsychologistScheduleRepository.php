<?php

namespace App\Repositories;

use App\Models\PsychologistSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PsychologistScheduleRepository
{
    /**
     * Obtiene todos los usuarios con rol 'psychology' con sus horarios configurados
     */
    public function getPsychologistsWithSchedules(): Collection
    {
        return User::with(['psychologistSchedules' => function ($query) {
                $query->orderBy('day_of_week');
            }, 'role'])
            ->whereHas('role', function ($query) {
                $query->where('name', 'psychology');
            })
            ->whereNull('client_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtiene los horarios configurados de un psicólogo específico
     */
    public function getSchedulesByUserId(string $userId): Collection
    {
        return PsychologistSchedule::where('user_id', $userId)
            ->orderBy('day_of_week')
            ->get();
    }

    /**
     * Sincroniza la semana de trabajo del psicólogo
     */
    public function syncSchedule(string $userId, array $schedulesData): array
    {
        return DB::transaction(function () use ($userId, $schedulesData) {
            $saved = [];

            foreach ($schedulesData as $item) {
                $schedule = PsychologistSchedule::updateOrCreate(
                    [
                        'user_id'     => $userId,
                        'day_of_week' => $item['day_of_week'],
                    ],
                    [
                        'id'                    => (string) Str::uuid(),
                        'start_time'            => $item['start_time'],
                        'end_time'              => $item['end_time'],
                        'slot_duration_minutes' => $item['slot_duration_minutes'] ?? 60,
                        'is_active'             => (bool) $item['is_active'],
                    ]
                );

                $saved[] = $schedule;
            }

            return $saved;
        });
    }
}
