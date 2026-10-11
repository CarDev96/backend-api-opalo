<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamineeRequest;
use App\Repositories\ExamineeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamineeController extends Controller
{
    public function __construct(
        private ExamineeRepository $examineeRepo
    ) {}

    /**
     * Listado de evaluados del cliente
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $request->user()->client_id;

        if (!$clientId) {
            return response()->json(['message' => 'El usuario no tiene una empresa cliente vinculada.'], 403);
        }

        $examinees = $this->examineeRepo->getByClient($clientId);

        return response()->json($examinees);
    }

    /**
     * Registrar nuevo evaluado
     */
    public function store(StoreExamineeRequest $request): JsonResponse
    {
        $clientId = $request->user()->client_id;

        $examinee = $this->examineeRepo->createForClient(
            $clientId,
            $request->validated()
        );

        return response()->json([
            'message' => 'Evaluado registrado exitosamente. Se ha generado su expediente.',
            'data'    => $examinee,
        ], 201);
    }

    /**
     * Eliminar evaluado
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $clientId = $request->user()->client_id;

        $this->examineeRepo->delete($id, $clientId);

        return response()->json([
            'message' => 'Evaluado removido del listado correctamente.',
        ]);
    }
}
