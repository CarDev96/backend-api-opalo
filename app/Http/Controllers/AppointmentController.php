<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookAppointmentRequest;
use App\Repositories\AppointmentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentRepository $appointmentRepo
    ) {}

    /**
     * Catálogos para el formulario de agendamiento
     */
    public function getFormData(Request $request): JsonResponse
    {
        return response()->json([
            'evaluation_types' => $this->appointmentRepo->getActiveEvaluationTypes(),
            'psychologists'    => $this->appointmentRepo->getAvailablePsychologists(),
        ]);
    }

    /**
     * Consulta los horarios libres en tiempo real
     */
    public function getSlots(Request $request): JsonResponse
    {
        $request->validate([
            'psychologist_id' => 'required|uuid',
            'date'            => 'required|date',
        ]);

        $slots = $this->appointmentRepo->getAvailableSlots(
            $request->query('psychologist_id'),
            $request->query('date')
        );

        return response()->json($slots);
    }

    /**
     * Guarda la cita
     */
    public function store(BookAppointmentRequest $request): JsonResponse
    {
        $clientId = $request->user()->client_id;

        if (!$clientId) {
            return response()->json(['message' => 'Usuario sin empresa asociada.'], 403);
        }

        $appointment = $this->appointmentRepo->bookAppointment($clientId, $request->validated());

        return response()->json([
            'message'     => 'Cita agendada exitosamente. Se ha notificado al especialista.',
            'appointment' => $appointment,
        ], 201);
    }

    /**
     * Citas del cliente
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $request->user()->client_id;

        if (!$clientId) {
            return response()->json(['message' => 'Usuario sin empresa asociada.'], 403);
        }

        $appointments = $this->appointmentRepo->getClientAppointments($clientId);

        return response()->json($appointments);
    }
}
