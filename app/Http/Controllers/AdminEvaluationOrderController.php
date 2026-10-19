<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminValidatePaymentRequest;
use App\Repositories\PaymentFileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminEvaluationOrderController extends Controller
{
    public function __construct(
        private PaymentFileRepository $paymentFileRepo
    ) {}

    /**
     * Listado general de órdenes para Administración y Ventas
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'payment_status' => $request->get('payment_status', 'all'),
            'search'         => $request->get('search'),
        ];

        $orders = $this->paymentFileRepo->getAllOrdersForStaff($filters);

        return response()->json([
            'data' => $orders,
        ]);
    }

    /**
     * Transmisión directa del comprobante desde S3/Local al navegador
     */
    public function streamReceipt(string $id): StreamedResponse|JsonResponse
    {
        $fileData = $this->paymentFileRepo->getReceiptStream($id);

        if (!$fileData) {
            return response()->json([
                'message' => 'El comprobante no fue encontrado en el almacenamiento.'
            ], 404);
        }

        $headers = [
            'Content-Type'        => $fileData['mime'],
            'Content-Disposition' => 'inline; filename="' . $fileData['title'] . '"',
        ];

        return Storage::disk($fileData['disk'])->response(
            $fileData['path'],
            $fileData['title'],
            $headers
        );
    }

    /**
     * Valida el pago (Aprobar o Rechazar) y notifica al cliente
     */
    public function validatePayment(AdminValidatePaymentRequest $request, string $id): JsonResponse
    {
        $updatedOrder = $this->paymentFileRepo->validateOrderPayment($id, $request->validated());

        return response()->json([
            'message' => $updatedOrder->payment_status === 'approved'
                ? 'Pago aprobado exitosamente. Se notificó al cliente.'
                : 'Pago rechazado. Se notificó al cliente el motivo del rechazo.',
            'data'    => $updatedOrder,
        ]);
    }
}
