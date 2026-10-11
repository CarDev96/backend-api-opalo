<?php

namespace App\Repositories;

use App\Models\Examinee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ExamineeRepository
{
    /**
     * Obtiene los evaluados pertenecientes al cliente autenticado
     */
    public function getByClient(string $clientId): Collection
    {
        return Examinee::where('client_id', $clientId)
            ->latest()
            ->get();
    }

    /**
     * Registra un nuevo evaluado y le genera su identificador de expediente base
     */
    public function createForClient(string $clientId, array $data): Examinee
    {
        // Generador de folio de expediente previo (ej: EXP-2026-XXXX)
        $year = date('Y');
        $random = strtoupper(Str::random(5));
        $fileNumber = "EXP-{$year}-{$random}";

        return Examinee::create([
            'client_id'   => $clientId,
            'full_name'   => trim($data['full_name']),
            'position'    => trim($data['position']),
            'phone'       => trim($data['phone']),
            'email'       => !empty($data['email']) ? trim($data['email']) : null,
            'curp'        => !empty($data['curp']) ? strtoupper(trim($data['curp'])) : null,
            'file_number' => $fileNumber,
            'status'      => 'registered',
        ]);
    }

    /**
     * Elimina lógicamente un evaluado (SoftDelete)
     */
    public function delete(string $id, string $clientId): bool
    {
        $examinee = Examinee::where('id', $id)
            ->where('client_id', $clientId)
            ->firstOrFail();

        return (bool) $examinee->delete();
    }
}
