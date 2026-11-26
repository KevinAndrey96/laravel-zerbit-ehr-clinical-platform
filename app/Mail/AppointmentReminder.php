<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class AppointmentReminder extends Mailable
{
    public function __construct(public readonly Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recordatorio de tu cita de fisioterapia');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.appointment-reminder', with: ['appointment' => $this->appointment]);
    }
}
