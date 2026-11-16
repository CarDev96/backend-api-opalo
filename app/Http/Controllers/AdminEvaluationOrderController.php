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

	public function store(Request $request): JsonResponse
	{
		$request->validate([
			'client_id'          => 'required|uuid|exists:clients,id',
			'examinee_id'        => 'required|uuid|exists:examinees,id',
			'evaluation_type_id' => 'required|uuid|exists:evaluation_types,id',
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
			'po_status'          => 'pending_po',
		]);

		if (in_array($examinee->status, ['pending', 'registered', null])) {
			$examinee->update(['status' => 'order_created']);
		}

		return response()->json([
			'message' => 'Orden de certificación generada exitosamente.',
			'order'   => $order->load(['client', 'examinee', 'evaluationType']),
		], 201);
	}

	/**
	 * Valida la Orden de Compra (Aprobar o Rechazar PO)
	 */
	public function validatePurchaseOrder(Request $request, string $id): JsonResponse
	{
		$request->validate([
			'status'           => 'required|in:po_approved,po_rejected',
			'rejection_reason' => 'required_if:status,po_rejected|nullable|string|max:500',
		]);

		$order = EvaluationOrder::findOrFail($id);

		$order->update([
			'po_status'           => $request->status,
			'po_rejection_reason' => $request->status === 'po_rejected' ? $request->rejection_reason : null,
		]);

		return response()->json([
			'message' => $request->status === 'po_approved' ? 'Orden de Compra aprobada exitosamente.' : 'Orden de Compra rechazada.',
			'order'   => $order->fresh(['client', 'examinee', 'evaluationType']),
		]);
	}

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

	public function uploadPurchaseOrder(Request $request, string $id): JsonResponse
	{
		$order = EvaluationOrder::findOrFail($id);

		$request->validate([
			'file' => 'required|file|mimes:pdf,jpeg,jpg,png,webp|max:15360',
		]);

		$uploaded = $request->file('file');

		if ($order->po_file_path) {
			$this->fileUploaderService->delete($order->po_file_path);
		}

		$uploadResult = $this->fileUploaderService->upload([
			'file'          => $uploaded,
			'root_folder'   => 'purchase-orders',
			'subfolders'    => [$order->client_id, 'orders'],
			'original_name' => $uploaded->getClientOriginalName(),
		]);

		$order->update([
			'po_file_path'        => $uploadResult['path'],
			'po_original_name'    => $uploadResult['original_name'],
			'po_status'           => 'po_under_review',
			'po_rejection_reason' => null,
		]);

		return response()->json([
			'message' => 'Orden de Compra recibida exitosamente.',
			'order'   => $order->fresh(['client', 'examinee', 'evaluationType']),
		]);
	}
}
