<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $recipientType // 'psychologist' | 'client'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'psychologist'
            ? 'Nueva Evaluación Asignada - Ópalo Certificados de Confianza'
            : 'Confirmación de Cita de Evaluación - Ópalo Certificados de Confianza';

        return new Envelope(
            subject: $subject,
        );
    }

	public function content(): Content
    {
        return new Content(
            view: 'mails.appointments.appointment-scheduled', // 👈 con .appointments.
        );
    }
}
