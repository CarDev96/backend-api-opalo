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

        // 2. Desglose completo de estatus de evaluados con sus órdenes
        $examinees = Examinee::with([
                'client:id,company_name,trade_name',
                'evaluationOrders' => function ($q) {
                    $q->orderBy('created_at', 'asc');
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $statusBreakdown = [
            'pending'          => $examinees->whereIn('status', ['pending', 'registered', null])->count(),
            'order_created'    => $examinees->where('status', 'order_created')->count(),
            'scheduled'        => $examinees->where('status', 'scheduled')->count(),
            'awaiting_results' => $examinees->whereIn('status', ['awaiting_results', 'attended'])->count(),
            'completed'        => $examinees->where('status', 'completed')->count(),
            'incidents'        => $examinees->whereIn('status', ['cancelled', 'no_show'])->count(),
        ];

        // 3. Cálculo de tiempo promedio de certificación desde la Orden de Evaluación hasta Cierre
        $completedWithOrder = $examinees->filter(function ($e) {
            return $e->status === 'completed' && $e->evaluationOrders->isNotEmpty();
        });

        $avgCompletionHours = 0;
        if ($completedWithOrder->count() > 0) {
            $totalHours = $completedWithOrder->sum(function ($e) {
                $firstOrder = $e->evaluationOrders->first();
                $start = Carbon::parse($firstOrder->created_at);
                $end = $e->completed_at ? Carbon::parse($e->completed_at) : Carbon::parse($e->updated_at);
                return $start->diffInHours($end);
            });
            $avgCompletionHours = round($totalHours / $completedWithOrder->count(), 1);
        }

        // 4. Listado de tiempos por expediente (medido desde la orden de evaluación)
        $examineeTimelines = $examinees->map(function ($e) {
            $firstOrder = $e->evaluationOrders->first();

            if (!$firstOrder) {
                return [
                    'id'           => $e->id,
                    'full_name'    => $e->full_name,
                    'curp'         => $e->curp,
                    'client_name'  => $e->client?->trade_name ?: ($e->client?->company_name ?: 'N/A'),
                    'status'       => $e->status ?: 'pending',
                    'has_order'    => false,
                    'order_date'   => null,
                    'completed_at' => $e->completed_at,
                    'elapsed_text' => 'Sin orden',
                    'total_hours'  => -1,
                ];
            }

            $start = Carbon::parse($firstOrder->created_at);
            $end = ($e->status === 'completed')
                ? ($e->completed_at ? Carbon::parse($e->completed_at) : Carbon::parse($e->updated_at))
                : Carbon::now();

            $diffHours = $start->diffInHours($end);
            $days = intdiv($diffHours, 24);
            $hours = $diffHours % 24;

            return [
                'id'           => $e->id,
                'full_name'    => $e->full_name,
                'curp'         => $e->curp,
                'client_name'  => $e->client?->trade_name ?: ($e->client?->company_name ?: 'N/A'),
                'status'       => $e->status ?: 'pending',
                'has_order'    => true,
                'order_date'   => $firstOrder->created_at,
                'completed_at' => $e->completed_at,
                'elapsed_text' => $days > 0 ? "{$days}d {$hours}h" : "{$hours}h",
                'total_hours'  => $diffHours,
            ];
        })->values();

        // 5. Próximas citas (a partir de hoy)
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
                'avg_completion_hours'   => $avgCompletionHours,
                'completed_count'        => $statusBreakdown['completed'],
            ],
            'status_breakdown'      => $statusBreakdown,
            'examinee_timelines'    => $examineeTimelines,
            'upcoming_appointments' => $upcomingAppointments,
        ]);
    }
}
