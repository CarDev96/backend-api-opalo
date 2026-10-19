<?php

namespace App\Http\Controllers;

use App\Repositories\PsychometricTestRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PsychometricTestController extends Controller
{
    public function __construct(
        protected PsychometricTestRepository $testRepo
    ) {}

    public function index(): JsonResponse
    {
        $tests = $this->testRepo->getAll();

        return response()->json([
            'data' => $tests,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file'        => 'required|file|mimes:pdf,doc,docx|max:20480', // Hasta 20MB
        ]);

        $test = $this->testRepo->create($validated, $request->file('file'));

        return response()->json([
            'message' => 'Prueba psicométrica registrada con éxito.',
            'data'    => $test,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file'        => 'nullable|file|mimes:pdf,doc,docx|max:20480',
        ]);

        $test = $this->testRepo->update($id, $validated, $request->file('file'));

        return response()->json([
            'message' => 'Prueba psicométrica actualizada con éxito.',
            'data'    => $test,
        ]);
    }

    public function toggleStatus(string $id): JsonResponse
    {
        $test = $this->testRepo->toggleStatus($id);

        return response()->json([
            'message' => 'Estatus de la prueba actualizado con éxito.',
            'data'    => $test,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->testRepo->delete($id);

        return response()->json([
            'message' => 'Prueba eliminada con éxito.',
        ]);
    }
}
