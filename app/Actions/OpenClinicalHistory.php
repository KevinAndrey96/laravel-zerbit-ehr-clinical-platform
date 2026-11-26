<?php

namespace App\Actions;

use App\Enums\AccessAction;
use App\Models\ClinicalHistory;
use App\Models\Patient;
use App\Models\User;
use App\Support\ClinicalAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Opens a treatment episode. A patient has at most one open history so that
 * session notes never end up split across parallel records.
 */
final readonly class OpenClinicalHistory
{
    public function __construct(private ClinicalAudit $audit) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Patient $patient, User $therapist, array $data): ClinicalHistory
    {
        $history = DB::transaction(function () use ($patient, $therapist, $data): ClinicalHistory {
            Patient::query()->whereKey($patient->id)->lockForUpdate()->first();
            if ($patient->clinicalHistories()->open()->exists()) {
                throw ValidationException::withMessages([
                    'reason' => 'El paciente ya tiene una historia clínica abierta. Dale de alta antes de abrir otra.',
                ]);
            }

            return $patient->clinicalHistories()->create([...$data, 'therapist_id' => $therapist->id]);
        });
        $this->audit->record($therapist, $history, AccessAction::Opened);

        return $history;
    }
}
