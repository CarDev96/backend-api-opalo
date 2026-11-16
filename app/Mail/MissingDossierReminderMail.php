<?php

namespace App\Mail;

use App\Models\Examinee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MissingDossierReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Examinee $examinee,
        public array $missingItems
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: 'Recordatorio: Documentación Pendiente - ' . $this->examinee->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.missing_dossier_reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
