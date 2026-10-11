<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Role;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function __construct(
		private UserRepository $userRepository
	) {}

	public function index(): JsonResponse
	{
		$users = $this->userRepository->getInternalUsers();

		// 2. Roles operativos exclusivos (excluimos 'client' y cualquier rol externo)
		$roles = $this->userRepository->getRolesWithoutClient();

		return response()->json([
			'users' => $users,
			'roles' => $roles,
		]);
	}

	public function roles(): JsonResponse
	{
		$roles = $this->userRepository->getRolesWithoutClient();

		return response()->json($roles);
	}

	public function store(StoreUserRequest $request): JsonResponse
	{
		$result = $this->userRepository->createUser($request->validated());

		return response()->json([
			'message' => 'Colaborador registrado exitosamente.',
			'data'    => [
				'user'             => $result['user'],
				'initial_password' => $result['plain_password'],
			],
		], 201);
	}

	public function changePassword(UpdatePasswordRequest $request): JsonResponse
	{
		$this->userRepository->updatePassword(
			$request->user(),
			$request->validated('password')
		);

		return response()->json([
			'message' => 'Contraseña actualizada exitosamente.',
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
