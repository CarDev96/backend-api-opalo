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
            subject: "Nuevo Comprobante de Pago Reportado - {$clientName} | Gcd Opalo certificaciones",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.payments.receipt-uploaded',
            with: [
                'paymentFile'  => $this->paymentFile,
                'client'       => $this->paymentFile->client,
                'order'        => $this->paymentFile->evaluationOrder,
                'examinee'     => $this->paymentFile->evaluationOrder?->examinee,
                'evalType'     => $this->paymentFile->evaluationOrder?->evaluationType,
                'bankAccount'  => $this->paymentFile->bankAccount,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
