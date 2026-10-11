<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppointmentSessionRequest;
use App\Repositories\ClinicalAppointmentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicalAppointmentController extends Controller
{
    public function __construct(
        protected ClinicalAppointmentRepository $clinicalRepo
    ) {}

    /**
     * Listado de citas del especialista autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $specialistId = $request->user()->id;
        $timeframe = $request->query('timeframe', 'today');

        $appointments = $this->clinicalRepo->getAppointmentsBySpecialist($specialistId, $timeframe);

        return response()->json($appointments);
    }

    /**
     * Actualiza la sesión (estatus y notas de la evaluación)
     */
    public function updateSession(UpdateAppointmentSessionRequest $request, string $id): JsonResponse
    {
        $specialistId = $request->user()->id;

        $appointment = $this->clinicalRepo->updateSession(
            $specialistId,
            $id,
            $request->validated()
        );

        return response()->json([
            'message'     => 'Sesión actualizada exitosamente.',
            'appointment' => $appointment,
        ]);
    }

    /**
     * Datos del Dashboard principal (/home) para el especialista
     */
    public function dashboard(Request $request): JsonResponse
    {
        $specialistId = $request->user()->id;

        $dashboardData = $this->clinicalRepo->getDashboardMetrics($specialistId);

        return response()->json($dashboardData);
    }
}
