<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class ChangeAppointmentStatus
{
    public function handle(Appointment $appointment, AppointmentStatus $status, ?string $reason = null): void
    {
        if (! in_array($status, $appointment->status->transitions(), true)) {
            throw ValidationException::withMessages([
                'status' => 'Una cita '.mb_strtolower($appointment->status->label()).' no puede pasar a '.mb_strtolower($status->label()).'.',
            ]);
        }
        $outcome = $status === AppointmentStatus::Attended || $status === AppointmentStatus::NoShow;
        if ($outcome && $appointment->starts_at->isAfter(CarbonImmutable::now())) {
            throw ValidationException::withMessages(['status' => 'La asistencia se registra cuando llega la hora de la cita.']);
        }
        if ($status === AppointmentStatus::Cancelled && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['cancellation_reason' => 'Indica el motivo de la cancelación.']);
        }

        $appointment->forceFill([
            'status' => $status,
            'cancellation_reason' => $status === AppointmentStatus::Cancelled ? $reason : $appointment->cancellation_reason,
        ])->save();
    }
}
