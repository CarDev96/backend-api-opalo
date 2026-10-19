<?php

namespace App\Http\Controllers;

use App\Repositories\ExamineePsychometricTestRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamineePsychometricTestController extends Controller
{
    public function __construct(
        protected ExamineePsychometricTestRepository $repo
    ) {}

    public function index(string $examineeId): JsonResponse
    {
        $tests = $this->repo->getForExaminee($examineeId);

        return response()->json([
            'data' => $tests,
        ]);
    }

    public function upload(Request $request, string $examineeId, string $testId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:20480',
        ]);

        $record = $this->repo->uploadTest($examineeId, $testId, $request->file('file'));

        return response()->json([
            'message'  => 'Prueba enviada a revisión exitosamente.',
            'document' => $record,
        ]);
    }
	
	public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status'           => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);

        $testRecord = \App\Models\ExamineePsychometricTest::findOrFail($id);

        $testRecord->update([
            'status'           => $validated['status'],
            'rejection_reason' => $validated['status'] === 'rejected' ? $validated['rejection_reason'] : null,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        // Despachar el Job a la cola para notificar al cliente vía SendEmailService
        \App\Jobs\NotifyClientPsychometricTestStatusJob::dispatch($testRecord->id);

        return response()->json([
            'message'  => 'Estatus de la prueba actualizado correctamente.',
            'document' => $testRecord->fresh(),
        ]);
    }
}
