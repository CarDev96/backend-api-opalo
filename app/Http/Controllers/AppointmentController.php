<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAppointmentController extends Controller
{
    public function __construct(
        protected AppointmentRepository $appointmentRepo
    ) {}

    public function formData(Request $request): JsonResponse
    {
        return response()->json([
            'clients'          => $this->appointmentRepo->getClientsWithEligibleExaminees(),
            'evaluation_types' => $this->appointmentRepo->getActiveEvaluationTypes(),
            'psychologists'    => $this->appointmentRepo->getAvailablePsychologists(),
        ]);
    }

    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'psychologist_id' => 'required',
            'date'            => 'required|date',
        ]);

        $slots = $this->appointmentRepo->getAvailableSlots(
            $request->query('psychologist_id'),
            $request->query('date')
        );

        return response()->json($slots);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'examinee_id'        => 'required|exists:examinees,id',
            'psychologist_id'    => 'required|exists:users,id',
            'evaluation_type_id' => 'required|exists:evaluation_types,id',
            'date'               => 'required|date',
            'start_time'         => 'required',
            'notes'              => 'nullable|string|max:500',
        ]);

        $appointment = $this->appointmentRepo->bookAppointment($validated);

        return response()->json([
            'message'     => 'Cita agendada exitosamente.',
            'appointment' => $appointment,
        ], 201);
    }

    public function dt(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['client', 'examinee', 'psychologist', 'evaluationType'])
            ->latest('date')
            ->get();

        return response()->json([
            'data' => $appointments,
        ]);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $appointment = Appointment::with('examinee')->findOrFail($id);

        DB::transaction(function () use ($appointment) {
            $appointment->update(['status' => 'cancelled']);

            if ($appointment->examinee) {
                $appointment->examinee->update(['status' => 'pending']);
            }
        });

        return response()->json([
            'message'     => 'Cita cancelada con éxito.',
            'appointment' => $appointment->fresh(),
        ]);
    }
}
