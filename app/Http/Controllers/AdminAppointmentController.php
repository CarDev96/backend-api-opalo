<?php

namespace App\Http\Controllers;

use App\Jobs\SendAppointmentScheduledNotification;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\EvaluationType;
use App\Models\Examinee;
use App\Models\User;
use App\Repositories\AppointmentRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function __construct(
        protected AppointmentRepository $appointmentRepo
    ) {}

    /**
     * Catálogos para el formulario de agendamiento de Administración:
     * - Empresas clientes activas
     * - Evaluados
     * - Tipos de evaluación
     * - Psicólogos disponibles con sus horarios
     */
    public function formData(): JsonResponse
    {
        $clients = Client::where('is_active', true)
            ->with(['examinees' => function ($q) {
                // Evaluados pendientes de cita
                $q->whereNull('status')->orWhere('status', 'registered');
            }])
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'trade_name']);

        $evaluationTypes = EvaluationType::where('is_active', true)
            ->orderBy('name')
            ->get();

        $psychologists = $this->appointmentRepo->getAvailablePsychologists();

        return response()->json([
            'clients'           => $clients,
            'evaluation_types'  => $evaluationTypes,
            'psychologists'     => $psychologists,
        ]);
    }

    /**
     * Obtener slots/horarios disponibles de un psicólogo en una fecha
     */
    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'psychologist_id' => 'required|uuid|exists:users,id',
            'date'            => 'required|date_format:Y-m-d',
        ]);

        $slots = $this->appointmentRepo->getAvailableSlots(
            $request->psychologist_id,
            $request->date
        );

        return response()->json($slots);
    }

    /**
     * Registrar la cita desde Administración
     */
	public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'          => 'required|uuid|exists:clients,id',
            'examinee_id'        => 'required|uuid|exists:examinees,id',
            'evaluation_type_id' => 'required|uuid|exists:evaluation_types,id',
            'psychologist_id'    => 'required|uuid|exists:users,id',
            'date'               => 'required|date_format:Y-m-d',
            'start_time'         => 'required|date_format:H:i',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $evalType = EvaluationType::find($data['evaluation_type_id']);
        $start = Carbon::createFromFormat('H:i', $data['start_time']);
        $end = $start->copy()->addMinutes(60)->format('H:i:s');

        $appointment = Appointment::create([
            'client_id'          => $data['client_id'],
            'examinee_id'        => $data['examinee_id'],
            'evaluation_type_id' => $data['evaluation_type_id'],
            'psychologist_id'    => $data['psychologist_id'],
            'date'               => $data['date'],
            'start_time'         => $data['start_time'] . ':00',
            'end_time'           => $end,
            'status'             => 'scheduled',
            'modality'           => $evalType->modality ?? 'presencial',
            'notes'              => $data['notes'] ?? null,
        ]);

        // Actualizar estatus del evaluado
        Examinee::where('id', $data['examinee_id'])->update(['status' => 'scheduled']);

        // Cargar relaciones
        $loadedAppointment = $appointment->load(['client', 'examinee', 'psychologist', 'evaluationType']);

        // Despachar el Job a la Queue de forma asíncrona
        SendAppointmentScheduledNotification::dispatch($loadedAppointment);

        return response()->json([
            'message'     => 'Cita agendada exitosamente. Las notificaciones han sido puestas en cola.',
            'appointment' => $loadedAppointment,
        ], 201);
    }

/**
     * Listado general de citas para la DataTable de Administración
     */
    public function dt(Request $request): JsonResponse
    {
        $appointments = Appointment::with([
            'examinee:id,full_name,position,phone,email',
            'client:id,company_name,trade_name,rfc,contact_email',
            'psychologist:id,name,email',
            'evaluationType:id,name,modality',
        ])
        ->orderBy('date', 'desc')
        ->orderBy('start_time', 'desc')
        ->get();

        return response()->json($appointments);
    }

    /**
     * Cancelar cita desde Administración
     */
    public function cancel(string $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'message'     => 'Cita cancelada exitosamente.',
            'appointment' => $appointment,
        ]);
    }
}
