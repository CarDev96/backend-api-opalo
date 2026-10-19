<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\EvaluationOrder;
use App\Models\Examinee;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        // 1. Métricas generales
        $totalClients = Client::count();
        $totalExaminees = Examinee::count();

        $scheduledAppointments = Appointment::whereIn('status', ['scheduled', 'in_progress'])->count();
        $todayAppointments = Appointment::where('date', $today)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->count();

        $pendingOrders = EvaluationOrder::where('payment_status', 'under_review')->count();

        // 2. Próximas citas (a partir de hoy)
        $upcomingAppointments = Appointment::with(['client', 'examinee', 'psychologist', 'evaluationType'])
            ->where('date', '>=', $today)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_clients'          => $totalClients,
                'total_examinees'        => $totalExaminees,
                'scheduled_appointments' => $scheduledAppointments,
                'today_appointments'     => $todayAppointments,
                'pending_orders'         => $pendingOrders,
            ],
            'upcoming_appointments' => $upcomingAppointments,
        ]);
    }
}
