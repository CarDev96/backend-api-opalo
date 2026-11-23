<?php

namespace App\Jobs;

use App\Mail\PsychometricTestUploadedMail;
use App\Models\Examinee;
use App\Models\ExamineePsychometricTest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyStaffPsychometricTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $examineeId,
        public string $testRecordId
    ) {}

    public function handle(): void
    {
        try {
            $examinee = Examinee::with([
                'client',
                'appointments' => function ($query) {
                    $query->whereNotIn('status', ['cancelled', 'no_show'])
                          ->with('psychologist');
                },
            ])->find($this->examineeId);

            $testRecord = ExamineePsychometricTest::with('psychometricTest')->find($this->testRecordId);

            if (!$examinee || !$testRecord) {
                Log::warning("NotifyStaffPsychometricTestJob: Registro no encontrado.");
                return;
            }

            $examineeName = $examinee->full_name ?? 'Candidato';
            $companyName  = $examinee->client?->company_name ?? 'Empresa Cliente';
            $testName     = $testRecord->psychometricTest?->name ?? 'Prueba Psicométrica';
            $originalName = $testRecord->original_name ?? 'Documento adjunto';

            // 1. Obtener correos del staff operativo (admin y trabajo social)
            $roleIds = Role::whereIn('name', ['admin', 'social_work'])->pluck('id');

            $recipientEmails = User::whereIn('role_id', $roleIds)
                ->whereNotNull('email')
                ->pluck('email');

            // 2. Si el evaluado ya tiene cita agendada/atendida, agregar al psicólogo asignado
            $psychologistEmails = $examinee->appointments
                ->pluck('psychologist.email')
                ->filter();

            // 3. Unificar y eliminar duplicados
            $allRecipients = $recipientEmails
                ->merge($psychologistEmails)
                ->map(fn ($email) => strtolower(trim($email)))
                ->unique()
                ->values();

            foreach ($allRecipients as $email) {
                Mail::to($email)->send(
                    new PsychometricTestUploadedMail(
                        $examineeName,
                        $companyName,
                        $testName,
                        $originalName
                    )
                );
            }
        } catch (\Throwable $e) {
            Log::error('Error en NotifyStaffPsychometricTestJob: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }
}