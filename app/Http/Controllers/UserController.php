<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->userRepository->getInternalUsers();

        // Roles operativos exclusivos (excluyendo 'client' y cualquier rol externo)
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

	public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $user = $this->userRepository->updateUser($id, $request->validated());

            return response()->json([
                'message' => 'Colaborador actualizado exitosamente.',
                'data'    => $user,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Colaborador no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el colaborador.',
                'error'   => $e->getMessage(),
            ], 500);
        }
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

	public function resetPassword(string $id): JsonResponse
    {
        try {
            $result = $this->userRepository->resetUserPassword($id);

            return response()->json([
                'message' => "Contraseña restablecida y enviada al correo ({$result['email']}).",
                'data'    => $result,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Colaborador no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Error al restablecer la contraseña.',
            ], 422);
        }
    }
}
