<?php

namespace App\Jobs;

use App\Mail\MissingDossierReminderMail;
use App\Models\Examinee;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMissingDossierReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public string $examineeId
    ) {}

    public function handle(): void
    {
        $examinee = Examinee::with(['client', 'documents', 'references'])->find($this->examineeId);
        if (!$examinee || !$examinee->client) {
            return;
        }

        $requiredSlots = [
            'birth_certificate' => 'Acta de Nacimiento',
            'rfc_document'      => 'RFC / Cédula Fiscal',
            'imss_nss'          => 'Nº de Afiliación IMSS',
            'ine_id'            => 'INE / Identificación Oficial',
            'curp_document'     => 'CURP',
            'afore_statement'   => 'Estado de Cuenta AFORE',
            'proof_of_address'  => 'Comprobante de Domicilio',
            'study_certificate' => 'Certificado de Estudios',
            'drivers_license'   => 'Licencia de Conducir',
            'candidate_photo'   => 'Fotografía de Rostro',
            'house_facade'      => 'Fotografía de Fachada',
            'house_living_room' => 'Fotografía de Sala / Comedor',
            'house_kitchen'     => 'Fotografía de Cocina',
        ];

        $uploadedTypes = $examinee->documents->pluck('document_type')->toArray();
        $missing = [];

        foreach ($requiredSlots as $type => $label) {
            if (!in_array($type, $uploadedTypes, true)) {
                $missing[] = $label;
            }
        }

        $laborLettersCount = $examinee->documents->where('document_type', 'labor_letters')->count();
        if ($laborLettersCount < 2) {
            $missing[] = 'Cartas Laborales / Recomendación (' . $laborLettersCount . ' de 2 mínimas)';
        }

        $refsCount = $examinee->references->count();
        if ($refsCount < 3) {
            $missing[] = 'Referencias Personales (' . $refsCount . ' de 3 requeridas)';
        }

        if (empty($missing)) {
            $missing[] = 'Revisión final de documentos pendientes de validación.';
        }

        $recipients = [];
        if (!empty($examinee->client->contact_email)) {
            $recipients[] = $examinee->client->contact_email;
        }

        $clientUserEmails = User::where('client_id', $examinee->client_id)
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();

        $recipients = array_values(array_unique(array_merge($recipients, $clientUserEmails)));

        if (empty($recipients)) {
            return;
        }

        Mail::to($recipients)->send(
            new MissingDossierReminderMail($examinee, $missing)
        );
    }
}
