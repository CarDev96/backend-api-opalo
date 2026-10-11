<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePaymentReceiptStatusRequest;
use App\Repositories\PaymentFileRepository;
use App\Services\SendEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentReceiptController extends Controller
{
	public function __construct(
		private PaymentFileRepository $paymentFileRepo,
		private SendEmailService $sendEmailService
	) {}

	public function index(Request $request): JsonResponse
	{
		$filters = $request->only(['status', 'client_id']);
		$receipts = $this->paymentFileRepo->getAllReceipts($filters);

		return response()->json($receipts);
	}

	public function updateStatus(UpdatePaymentReceiptStatusRequest $request, string $id): JsonResponse
	{
		$receipt = $this->paymentFileRepo->updateStatus($id, $request->validated());

		// Si cambió a aprobado o rechazado, disparamos el job de notificación al cliente
		if (in_array($receipt->status, ['approved', 'rejected'])) {
			$this->sendEmailService->notifyClientPaymentStatus($receipt);
		}

		return response()->json([
			'message' => 'Estatus de comprobante actualizado correctamente.',
			'data'    => $receipt,
		]);
	}
}
