<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentBookedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function envelope(): Envelope
    {
        $examineeName = $this->appointment->examinee?->full_name ?? 'Evaluado';
        $date = $this->appointment->date ? $this->appointment->date->format('d/m/Y') : '';

        return new Envelope(
            subject: "Nueva Cita Agendada: {$examineeName} ({$date} {$this->appointment->start_time})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.appointments.booked',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
