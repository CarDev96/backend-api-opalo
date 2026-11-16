<?php

namespace App\Repositories;

use App\Models\Examinee;
use App\Models\ExamineeDocument;
use App\Models\ExamineeReference;
use Illuminate\Support\Facades\DB;

class ExamineeDossierRepository
{
    /**
     * Obtener documentos agrupados: los únicos como objeto y los múltiples (cartas) como arreglo
     */
    public function getDocumentsByExaminee(string $examineeId): array
    {
        $allDocs = ExamineeDocument::where('examinee_id', $examineeId)->get();

        $singleDocs = [];
        $laborLetters = [];

        foreach ($allDocs as $doc) {
            if ($doc->document_type === 'labor_letters') {
                $laborLetters[] = $doc;
            } else {
                $singleDocs[$doc->document_type] = $doc;
            }
        }

        return [
            'singles'       => $singleDocs,
            'labor_letters' => $laborLetters,
        ];
    }

    public function getReferencesByExaminee(string $examineeId)
    {
        return ExamineeReference::where('examinee_id', $examineeId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function syncReferences(Examinee $examinee, array $references)
    {
        return DB::transaction(function () use ($examinee, $references) {
            $examinee->references()->forceDelete();

            foreach ($references as $ref) {
                $examinee->references()->create([
                    'full_name'    => $ref['full_name'],
                    'relationship' => $ref['relationship'],
                    'phone'        => $ref['phone'],
                    'years_known'  => $ref['years_known'] ?? null,
                    'occupation'   => $ref['occupation'] ?? null,
                    'status'       => 'pending',
                ]);
            }

            return $examinee->fresh(['references'])->references;
        });
    }

    /**
     * Retorna TODOS los evaluados para seguimiento y auditoría integral,
     * incluso si todavía no han subido documentos o referencias.
     */
    public function getDossiersForReview()
    {
        return Examinee::with([
            'client:id,company_name,trade_name,rfc,contact_email',
            'documents',
            'references',
            'psychometricTests.psychometricTest',
        ])
        ->orderBy('created_at', 'desc')
        ->get();
    }
}
