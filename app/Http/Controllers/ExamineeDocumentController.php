<?php

namespace App\Http\Controllers;

use App\Models\Examinee;
use App\Models\ExamineeDocument;
use App\Repositories\ExamineeDossierRepository;
use App\Services\FileUploaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamineeDocumentController extends Controller
{
    public function __construct(
        protected ExamineeDossierRepository $dossierRepo,
        protected FileUploaderService $fileUploaderService
    ) {}

    public function index(Request $request, string $examineeId): JsonResponse
    {
        $this->authorizeExaminee($request, $examineeId);
        $dossierData = $this->dossierRepo->getDocumentsByExaminee($examineeId);

        return response()->json($dossierData);
    }

    public function store(Request $request, string $examineeId): JsonResponse
    {
        $examinee = $this->authorizeExaminee($request, $examineeId);

        $validTypes = [
            'candidate_photo',
            'house_facade',
            'house_living_room',
            'house_kitchen',
            'birth_certificate',
            'rfc_document',
            'imss_nss',
            'ine_id',
            'curp_document',
            'afore_statement',
            'proof_of_address',
            'study_certificate',
            'drivers_license',
            'labor_letters',
        ];

        $request->validate([
            'document_type' => 'required|string|in:' . implode(',', $validTypes),
            'file'          => 'required|file|mimes:jpeg,jpg,png,webp,pdf|max:15360',
        ]);

        $type = $request->document_type;
        $uploadedFile = $request->file('file');

        // Subir archivo mediante FileUploaderService
        $uploadResult = $this->fileUploaderService->upload([
            'file'          => $uploadedFile,
            'root_folder'   => 'examinees',
            'subfolders'    => [$examinee->id, 'dossier'],
            'original_name' => $uploadedFile->getClientOriginalName(),
        ]);

        // 👉 Si es carta laboral, SIEMPRE se crea una nueva entrada
        if ($type === 'labor_letters') {
            $document = ExamineeDocument::create([
                'examinee_id'      => $examinee->id,
                'document_type'    => $type,
                'file_path'        => $uploadResult['path'],
                'original_name'    => $uploadResult['original_name'],
                'mime_type'        => $uploadResult['mime'],
                'file_size'        => $uploadedFile->getSize(),
                'status'           => 'pending',
                'rejection_reason' => null,
            ]);

            return response()->json([
                'message'  => 'Carta laboral agregada exitosamente.',
                'document' => $document->fresh(),
            ], 201);
        }

        // Para el resto de documentos (1 archivo por slot): buscar y reemplazar si ya existía
        $existing = ExamineeDocument::where('examinee_id', $examinee->id)
            ->where('document_type', $type)
            ->first();

        if ($existing && $existing->file_path) {
            $this->fileUploaderService->delete($existing->file_path);
        }

        $document = ExamineeDocument::updateOrCreate(
            ['examinee_id' => $examinee->id, 'document_type' => $type],
            [
                'file_path'        => $uploadResult['path'],
                'original_name'    => $uploadResult['original_name'],
                'mime_type'        => $uploadResult['mime'],
                'file_size'        => $uploadedFile->getSize(),
                'status'           => 'pending',
                'rejection_reason' => null,
                'reviewed_by'      => null,
                'reviewed_at'      => null,
            ]
        );

        return response()->json([
            'message'  => 'Documento subido y registrado exitosamente.',
            'document' => $document->fresh(),
        ], 201);
    }

    /**
     * Eliminar un archivo específico (ej. carta laboral errónea)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $doc = ExamineeDocument::findOrFail($id);
        $this->authorizeExaminee($request, $doc->examinee_id);

        if ($doc->file_path) {
            $this->fileUploaderService->delete($doc->file_path);
        }

        $doc->forceDelete();

        return response()->json(['message' => 'Documento eliminado correctamente.']);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status'           => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
        ]);

        $doc = ExamineeDocument::findOrFail($id);
        $doc->update([
            'status'           => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null,
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
        ]);

        return response()->json([
            'message'  => $request->status === 'approved' ? 'Documento aprobado.' : 'Documento rechazado.',
            'document' => $doc->fresh(),
        ]);
    }

    public function dossiersDataTable(): JsonResponse
    {
        $dossiers = $this->dossierRepo->getDossiersForReview();
        return response()->json($dossiers);
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
