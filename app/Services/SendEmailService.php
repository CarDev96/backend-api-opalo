<?php

namespace App\Services;

use App\Jobs\SendClientWelcomeEmailJob;
use App\Models\Client;
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
}
