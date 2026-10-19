<?php

namespace App\Jobs;

use App\Mail\PsychometricTestStatusUpdatedMail;
use App\Models\ExamineePsychometricTest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyClientPsychometricTestStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $testRecordId
    ) {}

    public function handle(): void
    {
        try {
            $record = ExamineePsychometricTest::with(['examinee.client', 'psychometricTest'])->find($this->testRecordId);

            if (!$record || !$record->examinee) {
                Log::warning("NotifyClientPsychometricTestStatusJob: No se encontró el registro ({$this->testRecordId})");
                return;
            }

            $client = $record->examinee->client;
            $clientEmail = $client?->contact_email;

            if (!$clientEmail) {
                Log::info("NotifyClientPsychometricTestStatusJob: El cliente no tiene correo de contacto registrado.");
                return;
            }

            $examineeName    = $record->examinee->full_name ?? 'Candidato';
            $companyName     = $client->company_name ?? 'Empresa';
            $testName        = $record->psychometricTest?->name ?? 'Prueba Psicométrica';
            $status          = $record->status;
            $rejectionReason = $record->rejection_reason;

            Mail::to($clientEmail)->send(
                new PsychometricTestStatusUpdatedMail(
                    examineeName: $examineeName,
                    companyName: $companyName,
                    testName: $testName,
                    status: $status,
                    rejectionReason: $rejectionReason
                )
            );
        } catch (\Throwable $e) {
            Log::error('Error en NotifyClientPsychometricTestStatusJob: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }
}
