<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public User $user,
        public string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecimiento de Contraseña - Portal GCD Ópalo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.client_password_reset',
        );
    }
}
