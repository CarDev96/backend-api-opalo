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
     * Listado de citas del especialista autenticado (Ruta oficial existente: /clinical/appointments/list)
     */
    public function index(Request $request): JsonResponse
    {
        $specialistId = $request->user()->id;
        $timeframe = $request->query('timeframe', 'all'); // Default 'all' para traer todo de inicio

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

    /**
     * MÉTODO NUEVO AGREGADO: Extracción limpia de todas las citas del especialista
     */
    public function listAllAppointments(Request $request): JsonResponse
    {
        $specialistId = $request->user()->id;
        $appointments = $this->clinicalRepo->getAppointmentsBySpecialist($specialistId, 'all');

        return response()->json([
            'success' => true,
            'data'    => $appointments,
        ]);
    }
}
