<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserRepository
{
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
}
