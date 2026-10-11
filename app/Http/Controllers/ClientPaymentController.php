<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPaymentReceiptRequest;
use App\Repositories\PaymentFileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    public function __construct(
        private PaymentFileRepository $paymentFileRepo
    ) {}

    public function getReceipts(Request $request): JsonResponse
    {
        $clientId = $request->user()->client_id;

        if (!$clientId) {
            return response()->json(['message' => 'Usuario sin empresa cliente asociada.'], 403);
        }

        $receipts = $this->paymentFileRepo->getReceiptsByClient($clientId);

        return response()->json($receipts);
    }

    public function uploadReceipt(UploadPaymentReceiptRequest $request): JsonResponse
    {
        $clientId = $request->user()->client_id;

        if (!$clientId) {
            return response()->json(['message' => 'Usuario sin empresa cliente asociada.'], 403);
        }

        $paymentFile = $this->paymentFileRepo->storePaymentReceipt(
            $clientId,
            $request->file('receipt'),
            $request->validated()
        );

        return response()->json([
            'message' => 'Comprobante recibido con éxito. En espera de validación.',
            'data'    => $paymentFile,
        ], 201);
    }
}
