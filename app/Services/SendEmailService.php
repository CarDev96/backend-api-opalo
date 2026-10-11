<?php

namespace App\Services;

use App\Jobs\SendAppointmentBookedEmailJob;
use App\Jobs\SendClientPaymentStatusJob;
use App\Jobs\SendClientWelcomeEmailJob;
use App\Jobs\SendStaffNewPaymentReceiptJob;
use App\Jobs\SendUserWelcomeEmailJob;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\ClientPaymentFile;
use App\Models\User;

class SendEmailService
{
	/**
	 * Encola el correo de bienvenida y entrega de credenciales para un nuevo cliente.
	 */
	public function sendClientWelcomeCredentials(Client $client, User $user, string $plainPassword): void
	{
		SendClientWelcomeEmailJob::dispatch($client, $user, $plainPassword);
	}

	public function sendUserWelcomeCredentials(User $user, string $plainPassword): void
	{
		SendUserWelcomeEmailJob::dispatch($user, $plainPassword);
	}

	/**
	 * Correo hardcodeado para la fase de pruebas
	 */
	private const TEST_EMAIL = 'azaelmv9601@gmail.com';

	/**
	 * Despacha el job para notificar al staff sobre un nuevo comprobante
	 */
	public function notifyStaffNewPaymentReceipt(ClientPaymentFile $paymentFile): void
	{
		SendStaffNewPaymentReceiptJob::dispatch($paymentFile, self::TEST_EMAIL);
	}

	/**
	 * Despacha el job para notificar al cliente sobre su estatus (Aprobado/Rechazado)
	 */
	public function notifyClientPaymentStatus(ClientPaymentFile $paymentFile): void
	{
		// En pruebas se envía a tu correo; en producción apuntará a $paymentFile->client?->contact_email
		SendClientPaymentStatusJob::dispatch($paymentFile, self::TEST_EMAIL);
	}

	public function notifyAppointmentBooked(Appointment $appointment): void
	{
		// En pruebas lo enviamos directo a tu correo hardcodeado:
		SendAppointmentBookedEmailJob::dispatch($appointment, self::TEST_EMAIL);
	}
}
