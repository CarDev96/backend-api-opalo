<?php

namespace App\Jobs;

use App\Mail\PaymentReceiptStatusUpdatedMail;
use App\Models\ClientPaymentFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendClientPaymentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ClientPaymentFile $paymentFile,
        public string $recipientEmail
    ) {}

    public function handle(): void
    {
        try {
            $this->paymentFile->loadMissing(['client', 'bankAccount']);

            Mail::to($this->recipientEmail)->send(new PaymentReceiptStatusUpdatedMail($this->paymentFile));

            Log::info("Job SendClientPaymentStatusJob completado ({$this->paymentFile->status}) hacia: {$this->recipientEmail}");
        } catch (\Throwable $e) {
            Log::error("Error en SendClientPaymentStatusJob: {$e->getMessage()}");
            throw $e;
        }
    }
}
