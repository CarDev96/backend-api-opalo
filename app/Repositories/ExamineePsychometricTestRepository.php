<?php

namespace App\Repositories;

use App\Jobs\NotifyStaffPsychometricTestJob;
use App\Models\Examinee;
use App\Models\ExamineePsychometricTest;
use App\Models\PsychometricTest;
use App\Services\FileUploaderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ExamineePsychometricTestRepository
{
    public function __construct(
        protected FileUploaderService $fileUploaderService
    ) {}

    public function getForExaminee(string $examineeId): Collection
    {
        $examinee = Examinee::findOrFail($examineeId);
        $catalogTests = PsychometricTest::where('is_active', true)->orderBy('name')->get();
        $uploaded = ExamineePsychometricTest::where('examinee_id', $examinee->id)
            ->get()
            ->keyBy('psychometric_test_id');

        return $catalogTests->map(function ($test) use ($uploaded) {
            $record = $uploaded->get($test->id);

            return [
                'test_id'          => $test->id,
                'name'             => $test->name,
                'description'      => $test->description,
                'status'           => $record ? $record->status : 'missing',
                'rejection_reason' => $record?->rejection_reason,
                'file_url'         => $record?->file_url,
                'original_name'    => $record?->original_name,
                'mime_type'        => $record?->mime_type,
                'file_path'        => $record?->file_path,
            ];
        });
    }

    public function uploadTest(string $examineeId, string $testId, UploadedFile $file): ExamineePsychometricTest
    {
        $examinee = Examinee::findOrFail($examineeId);
        $test = PsychometricTest::findOrFail($testId);

        $record = ExamineePsychometricTest::firstOrNew([
            'examinee_id'          => $examinee->id,
            'psychometric_test_id' => $test->id,
        ]);

        if ($record->file_path) {
            $this->fileUploaderService->delete($record->file_path);
        }

        $uploadResult = $this->fileUploaderService->upload([
            'file'        => $file,
            'root_folder' => "examinees/{$examinee->id}/psychometrics",
        ]);

        $path = is_array($uploadResult) ? ($uploadResult['file_path'] ?? $uploadResult['path']) : $uploadResult;

        $record->fill([
            'file_path'        => $path,
            'original_name'    => $file->getClientOriginalName(),
            'mime_type'        => $file->getClientMimeType(),
            'file_size'        => $file->getSize(),
            'status'           => 'pending',
            'rejection_reason' => null,
            'reviewed_by'      => null,
            'reviewed_at'      => null,
        ]);

        $record->save();

        // Despachar Job en cola para notificación por correo
        NotifyStaffPsychometricTestJob::dispatch($examinee->id, $record->id);

        return $record;
    }
}
