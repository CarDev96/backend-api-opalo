<?php

namespace App\Repositories;

use App\Models\BankAccount;
use App\Models\ClientPaymentFile;
use App\Services\FileUploaderService;
use App\Services\SendEmailService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentFileRepository
{
    protected FileUploaderService $filesUploaderService;
    protected SendEmailService $sendEmailService;

    public function __construct(
        FileUploaderService $filesUploaderService,
        SendEmailService $sendEmailService
    ) {
        $this->filesUploaderService = $filesUploaderService;
        $this->sendEmailService     = $sendEmailService;
    }

    /**
     * Consulta los comprobantes del cliente con su cuenta bancaria relacionada
     */
    public function getReceiptsByClient(string $clientId): Collection
    {
        return ClientPaymentFile::with('bankAccount')
            ->where('client_id', $clientId)
            ->latest()
            ->get();
    }

    /**
     * Consulta global para administración con filtros
     */
    public function getAllReceipts(array $filters = []): Collection
    {
        $query = ClientPaymentFile::with(['client', 'bankAccount'])->latest();

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query->get();
    }

    /**
     * Sube el archivo físicamente, registra en BD y despacha el Job de correo
     */
    public function storePaymentReceipt(string $clientId, UploadedFile $file, array $metadata): ClientPaymentFile
    {
        $uploaded = $this->filesUploaderService->upload([
            'file'        => $file,
            'root_folder' => 'payment-receipts',
            'subfolders'  => [$clientId],
        ]);

        $bankAccount = BankAccount::findOrFail($metadata['bank_account_id']);

        $paymentFile = DB::transaction(function () use ($clientId, $uploaded, $metadata, $bankAccount) {
            return ClientPaymentFile::create([
                'id'              => $uploaded['id'],
                'client_id'       => $clientId,
                'bank_account_id' => $bankAccount->id,
                'title'           => $uploaded['original_name'],
                'mime'            => $uploaded['mime'],
                'extension'       => $uploaded['extension'],
                'path'            => $uploaded['path'],
                'amount'          => !empty($metadata['amount']) ? $metadata['amount'] : null,
                'account_type'    => $bankAccount->type,
                'notes'           => $metadata['notes'] ?? null,
                'status'          => 'pending',
            ]);
        });

        // Aseguramos cargar las relaciones para el correo
        $paymentFile->load(['client', 'bankAccount']);

        // Ahora sí, $this->sendEmailService está definido y asignado
        $this->sendEmailService->notifyStaffNewPaymentReceipt($paymentFile);

        return $paymentFile;
    }

    /**
     * Actualiza el estatus de revisión
     */
    public function updateStatus(string $id, array $data): ClientPaymentFile
    {
        $receipt = ClientPaymentFile::with(['client', 'bankAccount'])->findOrFail($id);

        $receipt->update([
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? $receipt->notes,
        ]);

        return $receipt;
    }
}
