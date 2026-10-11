<?php

namespace App\Mail;

use App\Models\ClientPaymentFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClientPaymentFile $paymentFile
    ) {}

    public function envelope(): Envelope
    {
        $statusText = $this->paymentFile->status === 'approved' ? 'Aprobado' : 'Rechazado';

        return new Envelope(
            subject: "Actualización de tu Comprobante de Pago: {$statusText}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.payments.receipt-status-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
