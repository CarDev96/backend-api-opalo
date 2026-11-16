<?php

namespace App\Http\Controllers;

use App\Models\EvaluationOrder;
use App\Models\EvaluationType;
use App\Models\Examinee;
use App\Services\FileUploaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationOrderController extends Controller
{
    public function __construct(
        protected FileUploaderService $fileUploaderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = EvaluationOrder::with([
            'examinee:id,full_name,curp,position,status',
            'evaluationType:id,name,cost,modality',
        ])
        ->where('client_id', $user->client_id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'examinee_id'        => 'required|uuid|exists:examinees,id',
            'evaluation_type_id' => 'required|uuid|exists:evaluation_types,id',
        ]);

        $examinee = Examinee::where('id', $request->examinee_id)
            ->where('client_id', $user->client_id)
            ->firstOrFail();

        $evalType = EvaluationType::findOrFail($request->evaluation_type_id);

        $subtotal = (float) $evalType->cost;
        $vat = round($subtotal * 0.16, 2);
        $total = round($subtotal + $vat, 2);

        $order = EvaluationOrder::create([
            'client_id'          => $user->client_id,
            'examinee_id'        => $examinee->id,
            'evaluation_type_id' => $evalType->id,
            'subtotal'           => $subtotal,
            'vat'                => $vat,
            'total'              => $total,
            'payment_status'     => 'pending_receipt',
        ]);

        // Avanzar estatus del evaluado a 'order_created' si estaba recién registrado
        if (in_array($examinee->status, ['pending', 'registered', null])) {
            $examinee->update(['status' => 'order_created']);
        }

        return response()->json([
            'message' => 'Orden de certificación generada exitosamente.',
            'order'   => $order->load(['examinee', 'evaluationType']),
        ], 201);
    }

    public function uploadReceipt(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $order = EvaluationOrder::where('id', $id)
            ->where('client_id', $user->client_id)
            ->firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpeg,jpg,png,webp|max:15360',
        ]);

        $uploaded = $request->file('file');

        if ($order->receipt_file_path) {
            $this->fileUploaderService->delete($order->receipt_file_path);
        }

        $uploadResult = $this->fileUploaderService->upload([
            'file'          => $uploaded,
            'root_folder'   => 'payment-receipts',
            'subfolders'    => [$order->client_id, 'orders'],
            'original_name' => $uploaded->getClientOriginalName(),
        ]);

        $order->update([
            'receipt_file_path'     => $uploadResult['path'],
            'receipt_original_name' => $uploadResult['original_name'],
            'payment_status'        => 'under_review',
            'rejection_reason'      => null,
            'reviewed_by'           => null,
            'reviewed_at'           => null,
        ]);

        return response()->json([
            'message' => 'Comprobante de pago recibido. En espera de validación administrativa.',
            'order'   => $order->fresh(['examinee', 'evaluationType']),
        ]);
    }

    public function adminOrdersIndex(): JsonResponse
    {
        $orders = EvaluationOrder::with([
            'client:id,company_name,trade_name,rfc',
            'examinee:id,full_name,curp,status',
            'evaluationType:id,name,cost,modality',
        ])
        ->whereNotNull('receipt_file_path')
        ->orderBy('updated_at', 'desc')
        ->get();

        return response()->json($orders);
    }

    public function updatePaymentStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status'           => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
        ]);

        $order = EvaluationOrder::findOrFail($id);

        $order->update([
            'payment_status'   => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null,
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
        ]);

        return response()->json([
            'message' => $request->status === 'approved' ? 'Pago validado y aprobado exitosamente.' : 'Comprobante rechazado.',
            'order'   => $order->fresh(['client', 'examinee', 'evaluationType']),
        ]);
    }
}
