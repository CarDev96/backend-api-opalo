<?php

namespace App\Mail;

use App\Models\ClientPaymentFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClientPaymentFile $paymentFile
    ) {}

    public function envelope(): Envelope
    {
        $clientName = $this->paymentFile->client?->trade_name
            ?? $this->paymentFile->client?->company_name
            ?? 'Cliente';

        return new Envelope(
            subject: "Nuevo Comprobante de Pago Reportado - {$clientName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.payments.receipt-uploaded',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
