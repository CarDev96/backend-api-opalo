<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFiscalDocument;
use App\Services\FileUploaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientFiscalDocumentController extends Controller
{
    public function __construct(
        protected FileUploaderService $fileUploaderService
    ) {}

    /**
     * Obtener los datos fiscales vigentes de la empresa cliente
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $client = Client::findOrFail($user->client_id);

        $fiscalDoc = ClientFiscalDocument::where('client_id', $client->id)
            ->latest()
            ->first();

        return response()->json([
            'client'          => $client,
            'fiscal_document' => $fiscalDoc,
        ]);
    }

    /**
     * Subir o actualizar la Constancia de Situación Fiscal (PDF)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $client = Client::findOrFail($user->client_id);

        $validated = $request->validate([
            'rfc'           => ['required', 'string', 'min:12', 'max:13', 'regex:/^[A-Z&Ñ]{3,4}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{3}$/i'],
            'business_name' => ['required', 'string', 'max:200'],
            'fiscal_regime' => ['required', 'string', 'max:150'],
            'postal_code'   => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'file'          => ['required', 'file', 'mimes:pdf', 'max:15360'], // Solo PDF, hasta 15MB
        ], [
            'rfc.regex'          => 'El formato del RFC no es válido.',
            'postal_code.regex'  => 'El código postal debe constar de 5 dígitos.',
            'file.mimes'         => 'La Constancia de Situación Fiscal debe estar en formato PDF.',
            'file.max'           => 'El archivo no debe exceder los 15MB.',
        ]);

        $uploadedFile = $request->file('file');

        // Subir a S3
        $uploadResult = $this->fileUploaderService->upload([
            'file'          => $uploadedFile,
            'root_folder'   => 'clients',
            'subfolders'    => [$client->id, 'fiscal-documents'],
            'original_name' => $uploadedFile->getClientOriginalName(),
        ]);

        // Guardar registro obteniendo mime y tamaño directamente del archivo de Laravel
        $fiscalDoc = ClientFiscalDocument::create([
            'client_id'        => $client->id,
            'rfc'              => strtoupper(trim($validated['rfc'])),
            'business_name'    => strtoupper(trim($validated['business_name'])),
            'fiscal_regime'    => trim($validated['fiscal_regime']),
            'postal_code'      => trim($validated['postal_code']),
            'file_path'        => $uploadResult['path'],
            'original_name'    => $uploadResult['original_name'] ?? $uploadedFile->getClientOriginalName(),
            'mime_type'        => $uploadedFile->getClientMimeType() ?? 'application/pdf',
            'file_size'        => $uploadedFile->getSize() ?? 0,
            'status'           => 'pending',
            'rejection_reason' => null,
        ]);

        // Sincronizar datos base del cliente
        $client->update([
            'rfc'             => strtoupper(trim($validated['rfc'])),
            'company_name'    => strtoupper(trim($validated['business_name'])),
            'fiscal_regime'   => trim($validated['fiscal_regime']),
            'fiscal_zip_code' => trim($validated['postal_code']),
        ]);

        return response()->json([
            'message'         => 'Constancia de Situación Fiscal registrada exitosamente.',
            'fiscal_document' => $fiscalDoc,
        ], 201);
    }
}
