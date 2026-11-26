<?php

namespace Database\Factories;

use App\Enums\LabStatus;
use App\Enums\SampleType;
use App\Models\LabSample;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabSample>
 */
class LabSampleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'LAB-'.today()->year.'-'.fake()->unique()->numerify('######'),
            'patient_id' => Patient::factory(),
            'registered_by' => User::factory()->admin(),
            'sample_type' => SampleType::Blood,
            'exam' => 'Cuadro hemático',
            'taken_on' => today()->toDateString(),
            'status' => LabStatus::Registered,
            'notify_by_email' => true,
        ];
    }
}
