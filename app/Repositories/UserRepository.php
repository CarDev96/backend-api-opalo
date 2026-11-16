<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use App\Services\SendEmailService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $plainPassword = !empty($data['password'])
            ? $data['password']
            : Str::random(10);

        $role = Role::find($data['role_id']);
        $phone = ($role && $role->name === 'psychology')
            ? ($data['phone'] ?? null)
            : null;

        $user = DB::transaction(function () use ($data, $plainPassword, $phone) {
            return User::create([
                'name'      => trim($data['name']),
                'email'     => strtolower(trim($data['email'])),
                'role_id'   => $data['role_id'],
                'phone'     => $phone,
                'client_id' => null,
                'password'  => Hash::make($plainPassword),
            ]);
        });

        $user->load('role');

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

    public function resetUserPassword(string $id): array
    {
        $user = User::withTrashed()->findOrFail($id);

        $temporaryPassword = 'OPALO-' . strtoupper(Str::random(6));

        $user->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        $this->emailService->sendUserPasswordReset($user, $temporaryPassword);

        return [
            'email'              => $user->email,
            'temporary_password' => $temporaryPassword,
        ];
    }

    public function toggleStatus(string $id): bool
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            return $user->restore();
        }

        return (bool) $user->delete();
    }

    public function updateUser(string $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::withTrashed()->findOrFail($id);

            $role = Role::find($data['role_id']);
            $phone = ($role && $role->name === 'psychology')
                ? ($data['phone'] ?? $user->phone)
                : null;

            $user->update([
                'name'    => trim($data['name']),
                'email'   => strtolower(trim($data['email'])),
                'role_id' => $data['role_id'],
                'phone'   => $phone,
            ]);

            return $user->fresh(['role', 'client']);
        });
    }

    public function getRolesWithoutClient(): Collection
    {
        return Role::whereNotIn('name', ['client'])
            ->orderBy('description', 'asc')
            ->get(['id', 'name', 'description', 'display_name']);
    }
}
