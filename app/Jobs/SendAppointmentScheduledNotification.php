<?php

namespace App\Jobs;

use App\Mail\AppointmentScheduledMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentScheduledNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos en caso de falla
     */
    public int $tries = 3;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function handle(): void
    {
        // Asegurar que las relaciones estén cargadas
        $this->appointment->loadMissing(['client', 'examinee', 'psychologist', 'evaluationType']);

        // 1. Enviar a la Psicóloga asignada
        if ($this->appointment->psychologist?->email) {
            try {
                Mail::to($this->appointment->psychologist->email)
                    ->send(new AppointmentScheduledMail($this->appointment, 'psychologist'));
            } catch (\Throwable $e) {
                Log::error("Error enviando correo de cita a psicóloga ({$this->appointment->psychologist->email}): " . $e->getMessage());
            }
        }

        // 2. Enviar a la Empresa Cliente
        if ($this->appointment->client?->contact_email) {
            try {
                Mail::to($this->appointment->client->contact_email)
                    ->send(new AppointmentScheduledMail($this->appointment, 'client'));
            } catch (\Throwable $e) {
                Log::error("Error enviando correo de cita a cliente ({$this->appointment->client->contact_email}): " . $e->getMessage());
            }
        }
    }
}
