<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Valida que el rol del usuario autenticado esté dentro de los roles permitidos
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Cargar relación de rol si no está cargada
        if (! $user || ! in_array($user->role?->name, $roles)) {
            return response()->json([
                'message' => 'Acceso denegado: no tienes permisos para acceder a este recurso.'
            ], 403);
        }

        return $next($request);
    }
}
