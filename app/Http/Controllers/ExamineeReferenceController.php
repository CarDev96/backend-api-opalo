<?php

namespace App\Http\Controllers;

use App\Models\Examinee;
use App\Models\ExamineeReference;
use App\Repositories\ExamineeDossierRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamineeReferenceController extends Controller
{
    public function __construct(
        protected ExamineeDossierRepository $dossierRepo
    ) {}

    /**
     * Listar las referencias del evaluado
     */
    public function index(Request $request, string $examineeId): JsonResponse
    {
        $this->authorizeExaminee($request, $examineeId);
        $references = $this->dossierRepo->getReferencesByExaminee($examineeId);

        return response()->json($references);
    }

    /**
     * Guardar las 3 referencias personales obligatorias
     */
    public function store(Request $request, string $examineeId): JsonResponse
    {
        $examinee = $this->authorizeExaminee($request, $examineeId);

        $request->validate([
            'references'                => 'required|array|min:3|max:3',
            'references.*.full_name'    => 'required|string|max:150',
            'references.*.relationship' => 'required|string|max:80',
            'references.*.phone'        => 'required|string|max:15',
            'references.*.years_known'  => 'nullable|numeric|min:1|max:99',
            'references.*.occupation'   => 'nullable|string|max:100',
        ]);

        $savedReferences = $this->dossierRepo->syncReferences($examinee, $request->references);

        return response()->json([
            'message'    => 'Las referencias personales se guardaron correctamente.',
            'references' => $savedReferences,
        ], 201);
    }

    private function authorizeExaminee(Request $request, string $examineeId): Examinee
    {
        $examinee = Examinee::findOrFail($examineeId);
        $user = $request->user();
        $roleName = is_string($user->role) ? $user->role : ($user->role?->name ?? '');

        if ($roleName === 'client' && $examinee->client_id !== $user->client_id) {
            abort(403, 'No tienes autorización para acceder a los datos de este evaluado.');
        }

        return $examinee;
    }
}
