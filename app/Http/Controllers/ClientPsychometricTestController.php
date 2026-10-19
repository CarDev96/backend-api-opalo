<?php

namespace App\Http\Controllers;

use App\Models\PsychometricTest;
use Illuminate\Http\JsonResponse;

class ClientPsychometricTestController extends Controller
{
    /**
     * Retorna únicamente las pruebas psicométricas activas que tienen plantilla cargada
     */
    public function index(): JsonResponse
    {
        $tests = PsychometricTest::where('is_active', true)
            ->whereNotNull('file_path')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $tests,
        ]);
    }
}
