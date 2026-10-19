<?php

namespace App\Repositories;

use App\Models\PsychometricTest;
use App\Services\FileUploaderService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class PsychometricTestRepository
{
    public function __construct(
        protected FileUploaderService $fileUploaderService
    ) {}

    public function getAll(): Collection
    {
        return PsychometricTest::latest()->get();
    }

    public function findById(string $id): PsychometricTest
    {
        return PsychometricTest::findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $file = null): PsychometricTest
    {
        $payload = [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => true,
        ];

        if ($file) {
            $uploadResult = $this->fileUploaderService->upload([
                'file'        => $file,
                'root_folder' => 'psychometric_tests/templates',
            ]);

            $payload['file_path']     = is_array($uploadResult) ? ($uploadResult['file_path'] ?? $uploadResult['path']) : $uploadResult;
            $payload['original_name'] = $file->getClientOriginalName();
            $payload['mime_type']     = $file->getClientMimeType();
            $payload['file_size']     = $file->getSize();
        }

        return PsychometricTest::create($payload);
    }

    public function update(string $id, array $data, ?UploadedFile $file = null): PsychometricTest
    {
        $test = $this->findById($id);

        $payload = [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ];

        if ($file) {
            if ($test->file_path) {
                $this->fileUploaderService->delete($test->file_path);
            }

            $uploadResult = $this->fileUploaderService->upload([
                'file'        => $file,
                'root_folder' => 'psychometric_tests/templates',
            ]);

            $payload['file_path']     = is_array($uploadResult) ? ($uploadResult['file_path'] ?? $uploadResult['path']) : $uploadResult;
            $payload['original_name'] = $file->getClientOriginalName();
            $payload['mime_type']     = $file->getClientMimeType();
            $payload['file_size']     = $file->getSize();
        }

        $test->update($payload);

        return $test->fresh();
    }

    public function toggleStatus(string $id): PsychometricTest
    {
        $test = $this->findById($id);
        $test->update(['is_active' => !$test->is_active]);

        return $test->fresh();
    }

    public function delete(string $id): bool
    {
        $test = $this->findById($id);

        if ($test->file_path) {
            $this->fileUploaderService->delete($test->file_path);
        }

        return $test->delete();
    }
}
