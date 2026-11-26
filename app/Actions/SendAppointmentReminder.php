<?php

namespace App\Actions;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class SendAppointmentReminder
{
    public function handle(Appointment $appointment): void
    {
        $email = $appointment->patient->email;
        if ($email === null || $email === '') {
            throw ValidationException::withMessages(['reminder' => 'El paciente no tiene correo registrado.']);
        }
        if (! $appointment->status->holdsSlot()) {
            throw ValidationException::withMessages(['reminder' => 'Solo se recuerdan citas programadas o confirmadas.']);
        }

        Mail::to($email)->send(new AppointmentReminder($appointment));
        $appointment->forceFill(['reminder_sent_at' => CarbonImmutable::now()])->save();
    }
}
