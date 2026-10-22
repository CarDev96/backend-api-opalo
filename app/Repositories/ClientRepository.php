<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use App\Services\SendEmailService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class ClientRepository
{
    public function __construct(
        private SendEmailService $emailService
    ) {}

    public function getAllClients(): Collection
    {
        return Client::with(['users', 'fiscalDocument'])->latest()->get();
    }

    public function getClientById(string $id): ?Client
    {
        return Client::with(['users', 'fiscalDocument'])->find($id);
    }

    public function createClient(array $data): array
    {
        $result = DB::transaction(function () use ($data) {
            // 1. Registrar empresa cliente
            $client = Client::create($data);

            // 2. Obtener rol de cliente
            $clientRole = Role::where('name', 'client')->first();
            if (! $clientRole) {
                throw new Exception('El rol "client" no está registrado en la base de datos.');
            }

            // 3. Contraseña inicial basada en su RFC
            $initialPassword = strtoupper(trim($data['rfc']));

            // 4. Crear usuario vinculado (utilizando deleted_at nativo para bajas lógicas)
            $user = User::create([
                'client_id' => $client->id,
                'role_id'   => $clientRole->id,
                'name'      => $data['trade_name'] ?: $data['company_name'],
                'email'     => strtolower(trim($data['contact_email'])),
                'password'  => Hash::make($initialPassword),
            ]);

            return [
                'client'           => $client->fresh('fiscalDocument'),
                'user'             => $user,
                'initial_password' => $initialPassword,
            ];
        });

        // 5. Despachar correo a la cola
        $this->emailService->sendClientWelcomeCredentials(
            $result['client'],
            $result['user'],
            $result['initial_password']
        );

        return $result;
    }

	public function updateClient(string $id, array $data): Client
    {
        return DB::transaction(function () use ($id, $data) {
            $client = Client::findOrFail($id);
            $client->update($data);

            // Sincronizar el nombre y email en el usuario vinculado si cambió el contacto
            $user = User::where('client_id', $client->id)->first();
            if ($user) {
                $userUpdate = [];
                if (!empty($data['contact_email'])) {
                    $userUpdate['email'] = strtolower(trim($data['contact_email']));
                }
                if (!empty($data['trade_name']) || !empty($data['company_name'])) {
                    $userUpdate['name'] = $data['trade_name'] ?: $data['company_name'];
                }

                if (!empty($userUpdate)) {
                    $user->update($userUpdate);
                }
            }

            return $client->fresh(['users', 'fiscalDocument']);
        });
    }
}
