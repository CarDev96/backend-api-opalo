<?php

namespace App\Services;

use App\Jobs\SendAppointmentBookedEmailJob;
use App\Jobs\SendClientPasswordResetJob;
use App\Jobs\SendClientPaymentStatusJob;
use App\Jobs\SendClientWelcomeEmailJob;
use App\Jobs\SendStaffNewPaymentReceiptJob;
use App\Jobs\SendUserPasswordResetJob;
use App\Jobs\SendUserWelcomeEmailJob;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\ClientPaymentFile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendEmailService
{
    /**
     * Encola el correo de bienvenida y entrega de credenciales para un nuevo cliente.
     */
    public function sendClientWelcomeCredentials(Client $client, User $user, string $plainPassword): void
    {
        SendClientWelcomeEmailJob::dispatch($client, $user, $plainPassword);
    }

    /**
     * Encola el correo de restablecimiento de contraseña temporal para un cliente.
     */
    public function sendClientPasswordReset(Client $client, User $user, string $plainPassword): void
    {
        SendClientPasswordResetJob::dispatch($client, $user, $plainPassword);
    }

    /**
     * Encola el correo de bienvenida para un usuario interno del staff.
     */
    public function sendUserWelcomeCredentials(User $user, string $plainPassword): void
    {
        SendUserWelcomeEmailJob::dispatch($user, $plainPassword);
    }

    /**
     * Despacha el job para notificar al staff sobre un nuevo comprobante subido por el cliente.
     */
    public function notifyStaffNewPaymentReceipt(ClientPaymentFile $paymentFile): void
    {
        $roleIds = Role::whereIn('name', ['admin', 'social_work'])->pluck('id');

        $staffUsers = User::whereIn('role_id', $roleIds)
            ->whereNotNull('email')
            ->get();

        if ($staffUsers->isEmpty()) {
            Log::warning('notifyStaffNewPaymentReceipt: No se encontraron usuarios con rol admin o social_work para notificar.');
            return;
        }

        foreach ($staffUsers as $staff) {
            SendStaffNewPaymentReceiptJob::dispatch($paymentFile, $staff->email);
        }
    }

    /**
     * Despacha el job para notificar al cliente sobre el estatus de su comprobante (Aprobado/Rechazado).
     */
    public function notifyClientPaymentStatus(ClientPaymentFile $paymentFile): void
    {
        $paymentFile->loadMissing('client');
        $recipientEmail = $paymentFile->client?->contact_email;

        if (!$recipientEmail) {
            Log::warning("notifyClientPaymentStatus: El cliente asociado al pago ({$paymentFile->id}) no tiene contact_email.");
            return;
        }

        SendClientPaymentStatusJob::dispatch($paymentFile, $recipientEmail);
    }

    /**
     * Despacha el job para notificar la reserva de cita al cliente y a la especialista asignada.
     */
    public function notifyAppointmentBooked(Appointment $appointment): void
    {
        $appointment->loadMissing(['client', 'psychologist']);

        if ($appointment->client?->contact_email) {
            SendAppointmentBookedEmailJob::dispatch($appointment, $appointment->client->contact_email);
        }

        if ($appointment->psychologist?->email) {
            SendAppointmentBookedEmailJob::dispatch($appointment, $appointment->psychologist->email);
        }
    }

	/**
     * Encola el correo de restablecimiento de contraseña temporal para un usuario interno del staff.
     */
    public function sendUserPasswordReset(User $user, string $plainPassword): void
    {
        SendUserPasswordResetJob::dispatch($user, $plainPassword);
    }
}
