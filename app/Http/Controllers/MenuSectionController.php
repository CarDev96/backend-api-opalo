<?php

namespace App\Http\Controllers;

use App\Models\MenuSection;
use App\Models\RoleMenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuSectionController extends Controller
{
    /**
     * Retorna el árbol de secciones e ítems permitidos para el rol del usuario autenticado
     */
    public function navigation(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->role_id) {
            return response()->json([]);
        }

        // 1. Obtener los IDs de ítems que este rol tiene autorizados
        $allowedItemIds = RoleMenuAccess::where('role_id', $user->role_id)
            ->pluck('menu_item_id');

        // 2. Traer las secciones con sus ítems activos, ordenados
        $sections = MenuSection::with(['menuItems' => function ($query) use ($allowedItemIds) {
            $query->whereIn('id', $allowedItemIds)
				->where('is_visible', true)
				->orderBy('order_index');
        }])
        ->whereHas('menuItems', function ($query) use ($allowedItemIds) {
            $query->whereIn('id', $allowedItemIds)
				->where('is_visible', true);
        })
        ->orderBy('order_index')
        ->get();

        return response()->json($sections);
    }

    public function index(): JsonResponse
    {
        return response()->json(MenuSection::with('menuItems')->orderBy('order_index')->get());
    }
}
