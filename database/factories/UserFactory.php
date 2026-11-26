<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'last_name' => fake()->lastName().' '.fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('clave-de-prueba-1'),
            'role' => Role::Therapist,
            'document' => (string) fake()->unique()->numberBetween(10_000_000, 1_199_999_999),
            'professional_registry' => 'RETHUS-'.fake()->unique()->numberBetween(100_000, 999_999),
            'phone' => '3'.fake()->numerify('#########'),
            'active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => Role::Admin, 'professional_registry' => null]);
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
