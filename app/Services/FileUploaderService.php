<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploaderService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 's3');
    }

    /**
     * Sube cualquier archivo a S3/MinIO construyendo la ruta dinámica.
     *
     * @param array $payload [
     *     'file'        => UploadedFile|string (Base64 o instancia de archivo),
     *     'root_folder' => string (ej. 'payment-receipts', 'avatars', 'downloads', 'examinees'),
     *     'subfolders'  => array (opcional, ej. ['client-uuid', 'birth-certificates']),
     *     'ext'         => string (opcional si es base64, si es UploadedFile se toma directo),
     *     'original_name' => string (opcional para metadata)
     * ]
     */
    public function upload(array $payload): array
    {
        $fileId = (string) Str::uuid();

        // 1. Armar la ruta dinámica: root_folder / subfolder1 / subfolder2 / ...
        $segments = array_merge(
            [$payload['root_folder']],
            $payload['subfolders'] ?? []
        );

        // Limpiar diagonales y unir segmentos: ej. "payment-receipts/UUID-CLIENTE" o "avatars/users"
        $directory = implode('/', array_filter($segments));

        // 2. Procesar el archivo (soporte para UploadedFile o Base64)
        $fileInput = $payload['file'];

        if ($fileInput instanceof UploadedFile) {
            $extension    = strtolower($fileInput->getClientOriginalExtension());
            $originalName = $payload['original_name'] ?? $fileInput->getClientOriginalName();
            $mime         = $fileInput->getClientMimeType();
            $filename     = "{$fileId}.{$extension}";
            $fullPath     = "{$directory}/{$filename}";

            Storage::disk($this->disk)->putFileAs($directory, $fileInput, $filename);
        } else {
            // Manejo Base64
            $cleanBase64 = preg_replace('#^data:\w+/\w+;base64,#i', '', $fileInput);
            $binaryData  = base64_decode($cleanBase64);
            $extension   = strtolower($payload['ext'] ?? 'jpg');
            $originalName = $payload['original_name'] ?? "{$fileId}.{$extension}";
            $mime        = $payload['mime'] ?? 'image/jpeg';
            $filename    = "{$fileId}.{$extension}";
            $fullPath    = "{$directory}/{$filename}";

            Storage::disk($this->disk)->put($fullPath, $binaryData);
        }

        return [
            'id'            => $fileId,
            'original_name' => $originalName,
            'stored_name'   => $filename,
            'extension'     => $extension,
            'mime'          => $mime,
            'path'          => $fullPath,
        ];
    }

    /**
     * URL temporal firmada para descarga o vista previa
     */
    public function getTemporaryUrl(string $path, int $minutes = 30): ?string
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes($minutes));
        }

        return null;
    }

    /**
     * Eliminar archivo
     */
    public function delete(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }
}
