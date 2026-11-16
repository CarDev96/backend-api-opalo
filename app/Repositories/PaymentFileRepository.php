<?php

namespace App\Repositories;

use App\Models\BankAccount;
use App\Models\ClientPaymentFile;
use App\Models\EvaluationOrder;
use App\Services\FileUploaderService;
use App\Services\SendEmailService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Appointment;

class PaymentFileRepository
{
    public function __construct(
        protected FileUploaderService $fileUploaderService,
        protected SendEmailService $sendEmailService
    ) {}

    /**
     * Consulta todas las órdenes para Staff (Admin / Ventas) con filtros
     */
    public function getAllOrdersForStaff(array $filters = []): Collection
    {
        $query = EvaluationOrder::with([
                'client',
                'examinee',
                'evaluationType',
                'paymentFiles.bankAccount',
        ])->latest();

        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->whereHas('examinee', function ($eq) use ($s) {
                    $eq->where('full_name', 'like', "%{$s}%")
                        ->orWhere('curp', 'like', "%{$s}%");
                })->orWhereHas('client', function ($cq) use ($s) {
                    $cq->where('company_name', 'like', "%{$s}%")
                        ->orWhere('trade_name', 'like', "%{$s}%");
                });
            });
        }

        $orders = $query->get();

        // Verificamos de forma precisa si esta orden específica ya tiene una cita agendada
        foreach ($orders as $order) {
            $order->has_appointment = Appointment::where('examinee_id', $order->examinee_id)
                ->where('evaluation_type_id', $order->evaluation_type_id)
                ->whereNull('deleted_at')
                ->exists();
        }

        return $orders;
    }

    /**
     * Obtiene los metadatos del archivo y su disco para streaming directo desde S3/Local
     */
    public function getReceiptStream(string $orderId): ?array
    {
        $order = EvaluationOrder::with('paymentFiles')->find($orderId);

        if (!$order) {
            return null;
        }

        $latestPayment = $order->paymentFiles()->latest()->first();

        $path  = $latestPayment?->path ?? $order->receipt_file_path;
        $title = $latestPayment?->title ?? $order->receipt_original_name ?? 'comprobante_pago.pdf';
        $mime  = $latestPayment?->mime ?? 'application/pdf';

        if (!$path) {
            return null;
        }

        if (Storage::disk('s3')->exists($path)) {
            return [
                'disk'  => 's3',
                'path'  => $path,
                'title' => $title,
                'mime'  => $mime,
            ];
        }

        if (Storage::disk('public')->exists($path)) {
            return [
                'disk'  => 'public',
                'path'  => $path,
                'title' => $title,
                'mime'  => $mime,
            ];
        }

        return null;
    }

    /**
     * Valida el pago de una orden (Aprobar o Rechazar) y notifica al cliente por correo
     */
    public function validateOrderPayment(string $orderId, array $data): EvaluationOrder
    {
        $order = EvaluationOrder::with(['client', 'examinee', 'evaluationType', 'paymentFiles.bankAccount'])
                ->findOrFail($orderId);

        DB::transaction(function () use ($order, $data) {
            $isApproved = $data['status'] === 'approved';

            $order->update([
                    'payment_status'   => $isApproved ? 'approved' : 'rejected',
                    'rejection_reason' => $isApproved ? null : ($data['rejection_reason'] ?? 'Comprobante no válido'),
            ]);

            $latestPaymentFile = $order->paymentFiles()->latest()->first();
            if ($latestPaymentFile) {
                $latestPaymentFile->update([
                        'status' => $isApproved ? 'approved' : 'rejected',
                        'notes'  => $data['notes'] ?? ($isApproved ? 'Aprobado por administración' : $data['rejection_reason']),
                ]);

                $latestPaymentFile->loadMissing([
                        'client',
                        'bankAccount',
                        'evaluationOrder.examinee',
                        'evaluationOrder.evaluationType',
                ]);

                $this->sendEmailService->notifyClientPaymentStatus($latestPaymentFile);
            }
        });

        $order->has_appointment = Appointment::where('examinee_id', $order->examinee_id)
            ->where('evaluation_type_id', $order->evaluation_type_id)
            ->whereNull('deleted_at')
            ->exists();

        return $order;
    }

    /**
     * Obtiene los comprobantes de un cliente
     */
    public function getReceiptsByClient(string $clientId): Collection
    {
        return ClientPaymentFile::with(['bankAccount', 'evaluationOrder'])
                ->where('client_id', $clientId)
                ->latest()
                ->get();
    }

    public function storePaymentReceipt(string $clientId, UploadedFile $file, array $metadata): ClientPaymentFile
    {
        $uploaded = $this->fileUploaderService->upload([
                'file'        => $file,
                'root_folder' => 'payment-receipts',
                'subfolders'  => [$clientId],
        ]);

        $order = EvaluationOrder::where('client_id', $clientId)
                ->findOrFail($metadata['order_id']);

        $bankAccount = null;
        if (!empty($metadata['bank_account_id'])) {
            $bankAccount = BankAccount::find($metadata['bank_account_id']);
        }

        if (!$bankAccount) {
            $bankAccount = BankAccount::where('bank_name', 'LIKE', '%BBVA%')->first()
                    ?? BankAccount::first();
        }

        $paymentFile = DB::transaction(function () use ($clientId, $uploaded, $metadata, $bankAccount, $order) {
            $record = ClientPaymentFile::create([
                    'id'                  => $uploaded['id'],
                    'client_id'           => $clientId,
                    'evaluation_order_id' => $order->id,
                    'bank_account_id'     => $bankAccount?->id,
                    'title'               => $uploaded['original_name'],
                    'mime'                => $uploaded['mime'],
                    'extension'           => $uploaded['extension'],
                    'path'                => $uploaded['path'],
                    'amount'              => !empty($metadata['amount']) ? $metadata['amount'] : $order->total,
                    'account_type'        => $bankAccount?->type ?? 'fiscal',
                    'notes'               => $metadata['notes'] ?? null,
                    'status'              => 'pending',
            ]);

            $order->update([
                    'payment_status'        => 'under_review',
                    'receipt_file_path'     => $uploaded['path'],
                    'receipt_original_name' => $uploaded['original_name'],
                    'rejection_reason'      => null,
            ]);

            return $record;
        });

        $paymentFile->load([
                'client',
                'bankAccount',
                'evaluationOrder.examinee',
                'evaluationOrder.evaluationType',
        ]);

        $this->sendEmailService->notifyStaffNewPaymentReceipt($paymentFile);

        return $paymentFile;
    }
}
