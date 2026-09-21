<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Role;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
	public function __construct(
		private UserRepository $userRepository
	) {}

	public function index(): JsonResponse
	{
		$users = User::withTrashed()
			->whereNull('client_id')
			->with('role')
			->latest()
			->get();

		// Si tu tabla 'roles' tiene 'name' o 'description', mapeamos o seleccionamos display_name
		$roles = Role::whereNotIn('name', ['client'])
			->get()
			->map(function ($role) {
				return [
					'id'           => $role->id,
					'name'         => $role->name,
					// Usamos 'display_name' si existe, o 'description', o 'name'
					'display_name' => $role->display_name ?? $role->description ?? ucfirst($role->name),
				];
			});

		return response()->json([
			'users' => $users,
			'roles' => $roles,
		]);
	}

	public function store(StoreUserRequest $request): JsonResponse
	{
		// Forzamos que sea un usuario interno del equipo (client_id en null)
		$userData = $request->validated();
		$userData['client_id'] = null;

		$result = $this->userRepository->createUser($userData);

		return response()->json([
			'message' => 'Colaborador registrado exitosamente.',
			'data'    => [
				'user'             => $result['user'],
				'initial_password' => $result['plain_password'],
			],
		], 201);
	}

	public function toggleStatus(string $id): JsonResponse
	{
		$user = $this->userRepository->toggleStatus($id);

		return response()->json([
			'message' => $user->trashed() ? 'Acceso de colaborador suspendido.' : 'Acceso reactivado.',
			'user'    => $user,
		]);
	}

	public function destroy(string $id): JsonResponse
	{
		$this->userRepository->toggleStatus($id);

		return response()->json([
			'message' => 'Colaborador dado de baja del sistema.',
		]);
	}
}
