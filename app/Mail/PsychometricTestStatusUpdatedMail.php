<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PsychometricTestStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $examineeName,
        public string $companyName,
        public string $testName,
        public string $status,
        public ?string $rejectionReason = null
    ) {}

    public function build(): static
    {
        $statusLabel = $this->status === 'approved' ? 'Aprobada' : 'Rechazada';

        return $this->subject("Dictamen de Prueba Psicométrica ({$statusLabel}) - {$this->examineeName}")
            ->view('mails.psychometric_test_status_updated')
            ->with([
                'examineeName'    => $this->examineeName,
                'companyName'     => $this->companyName,
                'testName'        => $this->testName,
                'status'          => $this->status,
                'statusLabel'     => $statusLabel,
                'rejectionReason' => $this->rejectionReason,
            ]);
    }
}
