<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\SendEmailService;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserRepository
{
	public function __construct(
		private SendEmailService $emailService
	) {}

	public function getAllUsers(): Collection
	{
		return User::with(['role', 'client'])->latest()->get();
	}

	public function getUserByClientId(string $clientId): ?User
	{
		return User::with(['role', 'client'])->where('client_id', $clientId)->first();
	}

	public function getInternalUsers(): Collection
	{
		return User::withTrashed()
			->whereNull('client_id')
			->with('role')
			->latest()
			->get();
	}

	public function createUser(array $data): array
	{
		// Si no mandaron contraseña manual, autogeneramos una temporal limpia de 10 caracteres
		$plainPassword = !empty($data['password'])
			? $data['password']
			: Str::random(10);

		$user = DB::transaction(function () use ($data, $plainPassword) {
			return User::create([
				'name'      => trim($data['name']),
				'email'     => strtolower(trim($data['email'])),
				'role_id'   => $data['role_id'],
				'client_id' => null,
				'password'  => Hash::make($plainPassword),
			]);
		});

		// Recargamos el rol asociado
		$user->load('role');

		// Envío asíncrono de credenciales por SendEmailService
		$this->emailService->sendUserWelcomeCredentials($user, $plainPassword);

		return [
			'user'           => $user,
			'plain_password' => $plainPassword,
		];
	}

	public function updatePassword(User $user, string $plainPassword): bool
	{
		return $user->update([
			'password' => Hash::make($plainPassword),
		]);
	}

	public function getRolesWithoutClient(): Collection
	{
		return Role::whereNotIn('name', ['client'])
			->orderBy('description', 'asc')
			->get(['id', 'name', 'description', 'display_name']);
	}
}
