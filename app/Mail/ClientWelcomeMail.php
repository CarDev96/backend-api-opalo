<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class ClientWelcomeMail extends Mailable
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
			from: new Address(
				config('mail.from.address'),
				config('mail.from.name')
			),
			subject: 'Bienvenido a Ópalo - Credenciales de Acceso a tu Portal',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.client_welcome',
		);
	}

	public function attachments(): array
	{
		return [];
	}
}
