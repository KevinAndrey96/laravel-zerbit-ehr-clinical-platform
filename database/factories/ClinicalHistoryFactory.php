<?php

namespace Database\Factories;

use App\Enums\FallRisk;
use App\Models\ClinicalHistory;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicalHistory>
 */
class ClinicalHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'therapist_id' => User::factory(),
            'opened_on' => today()->subDays(20)->toDateString(),
            'reason' => 'Dolor lumbar de tres semanas que empeora al estar sentado.',
            'diagnosis_code' => 'M54.5',
            'diagnosis' => 'Lumbago no especificado',
            'physio_diagnosis' => 'Limitación funcional por dolor lumbar mecánico con acortamiento de isquiotibiales.',
            'objective' => 'Disminuir el dolor y recuperar la tolerancia a la sedestación prolongada.',
            'authorized_sessions' => 10,
            'authorization_number' => 'AUT-'.fake()->numerify('######'),
            'copayment_cop' => 4_800,
            'background_pathological' => 'Niega',
            'pain_scale' => 7,
            'pain_notes' => 'Dolor punzante en región lumbar baja sin irradiación.',
            'joint_mobility' => 'Flexión de tronco limitada al 60 % por dolor.',
            'muscle_strength' => 'Abdominales 3/5, glúteos 4/5.',
            'fall_risk' => FallRisk::Low,
        ];
    }

    public function closed(): static
    {
        return $this->state(['closed_on' => today()->toDateString(), 'discharge_summary' => 'Paciente asintomático, cumple los objetivos del tratamiento.']);
    }
}
