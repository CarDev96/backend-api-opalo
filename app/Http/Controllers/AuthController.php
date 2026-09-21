<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
	public function login(Request $request): JsonResponse
	{
		$request->validate([
			'email' => 'required|email',
			'password' => 'required',
		]);

		$user = User::with(['role', 'client'])->where('email', $request->email)->first();

		if (! $user || ! Hash::check($request->password, $user->password)) {
			throw ValidationException::withMessages([
				'email' => ['Las credenciales ingresadas son incorrectas.'],
			]);
		}

		$token = $user->createToken('auth_token')->plainTextToken;

		return response()->json([
			'token' => $token,
			'user' => [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'role' => $user->role?->name,
				'role_display' => $user->role?->display_name,
				'default_route' => $user->role?->default_route ?? '/dashboard',
				'client' => $user->client ? [
					'id' => $user->client->id,
					'company_name' => $user->client->company_name,
					'rfc' => $user->client->rfc,
				] : null,
			],
		]);
	}

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('role'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}
