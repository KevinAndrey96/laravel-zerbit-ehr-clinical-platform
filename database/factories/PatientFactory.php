<?php

namespace Database\Factories;

use App\Enums\CivilStatus;
use App\Enums\DocumentType;
use App\Enums\Sex;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_type' => DocumentType::CitizenCard,
            'document_number' => (string) fake()->unique()->numberBetween(10_000_000, 1_199_999_999),
            'first_name' => fake()->firstName(),
            'second_name' => null,
            'first_surname' => fake()->lastName(),
            'second_surname' => fake()->lastName(),
            'birthdate' => fake()->dateTimeBetween('-75 years', '-18 years')->format('Y-m-d'),
            'sex' => fake()->randomElement([Sex::Female, Sex::Male]),
            'civil_status' => fake()->randomElement(CivilStatus::cases()),
            'insurer_id' => null,
            'occupation' => fake()->randomElement(['Docente', 'Contadora', 'Conductor', 'Estudiante', 'Enfermera', 'Comerciante']),
            'phone' => '3'.fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => 'Calle '.fake()->numberBetween(1, 180).' # '.fake()->numberBetween(1, 99).'-'.fake()->numberBetween(1, 99),
            'city' => 'Bogotá',
        ];
    }
}
