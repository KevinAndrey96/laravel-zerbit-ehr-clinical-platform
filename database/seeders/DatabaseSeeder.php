<?php

namespace Database\Seeders;

use App\Domain\Tax\Nit;
use App\Models\Insurer;
use Illuminate\Database\Seeder;

/**
 * Reference catalog every installation starts with. Safe to run again.
 * The insurers are fictitious; replace them with the real agreements.
 */
class DatabaseSeeder extends Seeder
{
    public const array INSURERS = [
        ['Salud Andina EPS', '900456781'],
        ['Vital Plus Medicina Prepagada', '901234560'],
        ['Protección Laboral ARL', '860123457'],
        ['Nueva Salud del Caribe EPS', '890321654'],
    ];

    public function run(): void
    {
        foreach (self::INSURERS as [$name, $number]) {
            Insurer::query()->updateOrCreate(['nit' => (string) Nit::parse($number)], ['name' => $name, 'active' => true]);
        }
    }
}
