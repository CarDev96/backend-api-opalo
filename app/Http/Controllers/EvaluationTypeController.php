<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvaluationTypeRequest;
use App\Http\Requests\UpdateEvaluationTypeRequest;
use App\Models\EvaluationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationTypeController extends Controller
{
    /**
     * Listar todos los tipos de evaluación activos (usado para catálogo de clientes y combos)
     */
    public function index(Request $request): JsonResponse
    {
        // Si se pide explícitamente ver todos (incluidos inactivos para panel de administración)
        $query = EvaluationType::query();

        if ($request->boolean('all')) {
            $types = $query->orderBy('name', 'asc')->get();
        } else {
            $types = $query->where('is_active', true)->orderBy('cost', 'desc')->get();
        }

        return response()->json($types);
    }

    /**
     * Crear un nuevo tipo de prueba/evaluación
     */
    public function store(StoreEvaluationTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $type = EvaluationType::create([
            'name'         => strtoupper(trim($validated['name'])),
            'modality'     => $validated['modality'],
            'cost'         => $validated['cost'],
            'description'  => $validated['description'] ?? null,
            'includes_vat' => $validated['includes_vat'] ?? false,
            'is_active'    => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Tipo de evaluación registrado exitosamente.',
            'data'    => $type,
        ], 201);
    }

    /**
     * Mostrar un tipo de evaluación específico
     */
    public function show(string $id): JsonResponse
    {
        $type = EvaluationType::findOrFail($id);

        return response()->json([
            'data' => $type,
        ]);
    }

    /**
     * Actualizar información de un tipo de prueba
     */
    public function update(UpdateEvaluationTypeRequest $request, string $id): JsonResponse
    {
        $type = EvaluationType::findOrFail($id);
        $validated = $request->validated();

        $type->update([
            'name'         => strtoupper(trim($validated['name'])),
            'modality'     => $validated['modality'],
            'cost'         => $validated['cost'],
            'description'  => $validated['description'] ?? null,
            'includes_vat' => $validated['includes_vat'] ?? false,
            'is_active'    => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Tipo de evaluación actualizado exitosamente.',
            'data'    => $type,
        ]);
    }

    /**
     * Eliminar (SoftDelete) un tipo de prueba
     */
    public function destroy(string $id): JsonResponse
    {
        $type = EvaluationType::findOrFail($id);

        // Validar si tiene órdenes generadas antes de eliminar
        if ($type->orders()->exists()) {
            return response()->json([
                'message' => 'No es posible eliminar el tipo de evaluación porque ya cuenta con órdenes registradas. Puedes desactivarlo.',
            ], 422);
        }

        $type->delete();

        return response()->json([
            'message' => 'Tipo de evaluación eliminado correctamente.',
        ]);
    }

    /**
     * Alternar estado activo / inactivo
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $type = EvaluationType::findOrFail($id);
        $type->is_active = !$type->is_active;
        $type->save();

        return response()->json([
            'message' => $type->is_active ? 'Tipo de evaluación activado.' : 'Tipo de evaluación desactivado.',
            'data'    => $type,
        ]);
    }
}
