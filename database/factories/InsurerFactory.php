<?php

namespace Database\Factories;

use App\Domain\Tax\Nit;
use App\Models\Insurer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insurer>
 */
class InsurerFactory extends Factory
{
    public function definition(): array
    {
        $number = (string) fake()->unique()->numberBetween(800_000_000, 999_999_999);

        return [
            'name' => 'EPS '.fake()->unique()->company(),
            'nit' => $number.'-'.Nit::checkDigitOf($number),
            'active' => true,
        ];
    }
}
