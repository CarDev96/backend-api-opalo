<?php

namespace App\Jobs;

use App\Mail\AppointmentBookedMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentBookedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $recipientEmail
    ) {}

    public function handle(): void
    {
        try {
            $this->appointment->loadMissing(['client', 'examinee', 'psychologist', 'evaluationType']);

            Mail::to($this->recipientEmail)->send(new AppointmentBookedMail($this->appointment));

            Log::info("Job SendAppointmentBookedEmailJob completado hacia: {$this->recipientEmail}");
        } catch (\Throwable $e) {
            Log::error("Error en SendAppointmentBookedEmailJob: {$e->getMessage()}");
            throw $e;
        }
    }
}
