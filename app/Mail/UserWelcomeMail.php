<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Ópalo - Credenciales de acceso al portal institucional',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.user_welcome',
            with: [
                'user'          => $this->user,
                'plainPassword' => $this->plainPassword,
                'roleName'      => $this->user->role?->description ?? $this->user->role?->name ?? 'Colaborador',
                'portalUrl'     => config('app.url') . '/login',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
