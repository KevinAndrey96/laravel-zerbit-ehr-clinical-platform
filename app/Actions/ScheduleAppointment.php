<?php

namespace App\Actions;

use App\Domain\Scheduling\TimeSlot;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Books a slot on a therapist's agenda without double booking the therapist
 * or the patient.
 */
final class ScheduleAppointment
{
    /**
     * @param  array{patient_id: int, therapist_id: int, starts_at: string, duration: int, notes?: string|null}  $data
     */
    public function handle(array $data): Appointment
    {
        $slot = TimeSlot::lasting(CarbonImmutable::parse($data['starts_at']), $data['duration']);

        return DB::transaction(function () use ($data, $slot): Appointment {
            User::query()->whereKey($data['therapist_id'])->lockForUpdate()->firstOrFail();
            if ($this->overlaps('therapist_id', $data['therapist_id'], $slot)) {
                throw ValidationException::withMessages(['starts_at' => 'El fisioterapeuta ya tiene una cita en ese horario.']);
            }
            if ($this->overlaps('patient_id', $data['patient_id'], $slot)) {
                throw ValidationException::withMessages(['starts_at' => 'El paciente ya tiene una cita en ese horario.']);
            }

            return Appointment::query()->create([
                'patient_id' => $data['patient_id'],
                'therapist_id' => $data['therapist_id'],
                'starts_at' => $slot->start,
                'ends_at' => $slot->end,
                'status' => AppointmentStatus::Scheduled,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    private function overlaps(string $column, int $id, TimeSlot $slot): bool
    {
        return Appointment::query()
            ->where($column, $id)
            ->holdingSlot()
            ->where('starts_at', '<', $slot->end)
            ->where('ends_at', '>', $slot->start)
            ->exists();
    }
}
