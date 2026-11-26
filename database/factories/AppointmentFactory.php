<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDay()->setTime(9, 0);

        return [
            'patient_id' => Patient::factory(),
            'therapist_id' => User::factory(),
            'starts_at' => $start,
            'ends_at' => $start->addMinutes(45),
            'status' => AppointmentStatus::Scheduled,
        ];
    }
}
