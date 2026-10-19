<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PsychometricTestUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $examineeName,
        public string $companyName,
        public string $testName,
        public string $originalName
    ) {}

    public function build(): static
    {
        return $this->subject('Prueba Psicométrica Pendiente de Revisión - ' . $this->examineeName)
            ->view('mails.psychometric_test_uploaded')
            ->with([
                'examineeName' => $this->examineeName,
                'companyName'  => $this->companyName,
                'testName'     => $this->testName,
                'originalName' => $this->originalName,
            ]);
    }
}
