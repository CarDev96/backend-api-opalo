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
        return Client::with('users')->latest()->get();
    }

	public function getClientById(string $id): ?Client
	{
		return Client::with('users')->find($id);
	}

    public function createClient(array $data): array
    {
        $result = DB::transaction(function () use ($data) {

			$client = Client::create([
							'company_name'    => $data['company_name'],
							'trade_name'      => $data['trade_name'],
							'rfc'             => strtoupper(trim($data['rfc'])),
							'contact_email'   => strtolower(trim($data['contact_email'])),
							'contact_phone'   => $data['contact_phone'] ?? null,
							'fiscal_regime'   => $data['fiscal_regime'] ?? null,
							'fiscal_zip_code' => $data['fiscal_zip_code'] ?? null,
							'is_active'       => true,
						]);

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
                'client'           => $client,
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
}
