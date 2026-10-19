<?php

namespace App\Jobs;

use App\Mail\PaymentReceiptUploadedMail;
use App\Models\ClientPaymentFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendStaffNewPaymentReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ClientPaymentFile $paymentFile,
        public string $recipientEmail
    ) {}

    public function handle(): void
    {
        try {
            // Aseguramos que todas las relaciones vengan cargadas en el job
            $this->paymentFile->loadMissing([
                'client',
                'bankAccount',
                'evaluationOrder.examinee',
                'evaluationOrder.evaluationType',
            ]);

            Mail::to($this->recipientEmail)->send(new PaymentReceiptUploadedMail($this->paymentFile));

            Log::info("Job SendStaffNewPaymentReceiptJob completado hacia: {$this->recipientEmail}");
        } catch (\Throwable $e) {
            Log::error("Error en SendStaffNewPaymentReceiptJob: {$e->getMessage()}");
            throw $e;
        }
    }
}
