<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminValidatePaymentRequest;
use App\Models\Client;
use App\Models\EvaluationOrder;
use App\Models\EvaluationType;
use App\Models\Examinee;
use App\Repositories\PaymentFileRepository;
use App\Services\FileUploaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminEvaluationOrderController extends Controller
{
    public function __construct(
        private PaymentFileRepository $paymentFileRepo,
        protected FileUploaderService $fileUploaderService
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
     * Catálogos para Generar Orden desde Admin / Ventas:
     * Retorna empresas clientes activas con sus evaluados registrados y los tipos de evaluación activos.
     */
    public function formData(): JsonResponse
    {
        $clients = Client::where('is_active', true)
            ->with(['examinees' => function ($query) {
                $query->select('id', 'client_id', 'full_name', 'curp', 'position', 'status')
                      ->orderBy('full_name', 'asc');
            }])
            ->orderBy('company_name', 'asc')
            ->get(['id', 'company_name', 'trade_name', 'rfc', 'requires_purchase_order']);

        $evaluationTypes = EvaluationType::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'cost', 'modality']);

        return response()->json([
            'clients'          => $clients,
            'evaluation_types' => $evaluationTypes,
        ]);
    }

    /**
     * Crea una Orden de Evaluación a nombre de una empresa cliente (Admin / Ventas)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'client_id'          => 'required|uuid|exists:clients,id',
            'examinee_id'        => 'required|uuid|exists:examinees,id',
            'evaluation_type_id' => 'required|uuid|exists:evaluation_types,id',
        ], [
            'client_id.required'          => 'Debes seleccionar una empresa cliente.',
            'examinee_id.required'        => 'Debes seleccionar un candidato / evaluado.',
            'evaluation_type_id.required' => 'Debes seleccionar el tipo de prueba.',
        ]);

        $examinee = Examinee::where('id', $request->examinee_id)
            ->where('client_id', $request->client_id)
            ->firstOrFail();

        $evalType = EvaluationType::findOrFail($request->evaluation_type_id);

        $subtotal = (float) $evalType->cost;
        $vat      = round($subtotal * 0.16, 2);
        $total    = round($subtotal + $vat, 2);

        $order = EvaluationOrder::create([
            'client_id'          => $request->client_id,
            'examinee_id'        => $examinee->id,
            'evaluation_type_id' => $evalType->id,
            'subtotal'           => $subtotal,
            'vat'                => $vat,
            'total'              => $total,
            'payment_status'     => 'pending_receipt',
        ]);

        return response()->json([
            'message' => 'Orden de certificación generada exitosamente.',
            'order'   => $order->load(['client', 'examinee', 'evaluationType']),
        ], 201);
    }

    /**
     * Permite a Admin / Ventas subir o reemplazar el comprobante de pago de una orden
     */
    public function uploadReceipt(Request $request, string $id): JsonResponse
    {
        $order = EvaluationOrder::findOrFail($id);

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
            'message' => 'Comprobante de pago recibido exitosamente.',
            'order'   => $order->fresh(['client', 'examinee', 'evaluationType']),
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
