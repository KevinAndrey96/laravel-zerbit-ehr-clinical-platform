<?php

namespace Database\Factories;

use App\Models\ClinicalHistory;
use App\Models\Evolution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evolution>
 */
class EvolutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinical_history_id' => ClinicalHistory::factory(),
            'therapist_id' => User::factory(),
            'session_number' => 1,
            'attended_on' => today()->subDays(2)->toDateString(),
            'starts_at' => '08:00',
            'ends_at' => '08:45',
            'pain_scale' => 5,
            'note' => 'Termoterapia, movilización lumbar y ejercicios de control motor. Tolera bien la sesión.',
        ];
    }
}
