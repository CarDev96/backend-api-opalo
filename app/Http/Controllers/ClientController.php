<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Repositories\ClientRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    private ClientRepository $clientRepository;
    private UserRepository $userRepository;

    public function __construct(ClientRepository $clientRepository, UserRepository $userRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
    }

    public function index(): JsonResponse
    {
        $clients = $this->clientRepository->getAllClients();
        return response()->json($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $result = $this->clientRepository->createClient($request->validated());

        return response()->json([
            'message' => 'Cliente y usuario de acceso creados con éxito.',
            'data'    => [
                'client'           => $result['client'],
                'user_email'       => $result['user']->email,
                'initial_password' => $result['initial_password'],
            ],
        ], 201);
    }

	public function destroy(string $id)
	{
		$client = $this->clientRepository->getClientById($id);
		$user = $this->userRepository->getUserByClientId($id);

		try {
			$client?->delete();
			$user?->delete();
			return response()->json(['message' => 'Cliente eliminado con éxito.']);
		} catch (\Exception $e) {
			return response()->json(['message' => 'Error al eliminar el cliente.', 'error' => $e->getMessage()], 500);
		}
	}

	public function toggleStatus(string $id)
	{
		$client = $this->clientRepository->getAllClients()->find($id);

		if (!$client) {
			return response()->json(['message' => 'Cliente no encontrado.'], 404);
		}

		try {
			$client->is_active = !$client->is_active;
			$client->save();

			return response()->json([
				'message' => 'Estado del cliente actualizado con éxito.',
				'data'    => [
					'client_id' => $client->id,
					'is_active' => $client->is_active,
				],
			]);
		} catch (\Exception $e) {
			return response()->json(['message' => 'Error al actualizar el estado del cliente.', 'error' => $e->getMessage()], 500);
		}
	}
}
