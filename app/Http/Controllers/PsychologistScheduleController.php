<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncPsychologistScheduleRequest;
use App\Repositories\PsychologistScheduleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PsychologistScheduleController extends Controller
{
    public function __construct(
        protected PsychologistScheduleRepository $scheduleRepo
    ) {}

    /**
     * Listado de psicólogos con sus configuraciones de horario
     */
    public function index(): JsonResponse
    {
        $psychologists = $this->scheduleRepo->getPsychologistsWithSchedules();
        return response()->json($psychologists);
    }

    /**
     * Horarios de un psicólogo en específico
     */
    public function getByPsychologist(string $id): JsonResponse
    {
        $schedules = $this->scheduleRepo->getSchedulesByUserId($id);
        return response()->json($schedules);
    }

    /**
     * Guarda / actualiza la matriz de horarios de un psicólogo
     */
    public function sync(SyncPsychologistScheduleRequest $request, string $id): JsonResponse
    {
        $saved = $this->scheduleRepo->syncSchedule($id, $request->validated()['schedules']);

        return response()->json([
            'message'   => 'Agenda de horarios actualizada exitosamente.',
            'schedules' => $saved,
        ]);
    }
}
