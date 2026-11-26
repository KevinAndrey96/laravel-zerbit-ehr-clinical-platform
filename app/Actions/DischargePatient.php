<?php

namespace App\Actions;

use App\Enums\AccessAction;
use App\Models\ClinicalHistory;
use App\Models\User;
use App\Support\ClinicalAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Closes a treatment episode with a discharge summary. After this the
 * history is read-only.
 */
final readonly class DischargePatient
{
    public function __construct(private ClinicalAudit $audit) {}

    public function handle(ClinicalHistory $history, User $therapist, string $closedOn, string $summary): void
    {
        DB::transaction(function () use ($history, $closedOn, $summary): void {
            $locked = ClinicalHistory::query()->whereKey($history->id)->lockForUpdate()->firstOrFail();
            $lastSession = $locked->evolutions()->max('attended_on');
            if (is_string($lastSession) && $closedOn < substr($lastSession, 0, 10)) {
                throw ValidationException::withMessages([
                    'closed_on' => 'El alta no puede ser anterior a la última sesión registrada.',
                ])->errorBag('discharge');
            }
            $locked->forceFill(['closed_on' => $closedOn, 'discharge_summary' => $summary])->save();
        });
        $this->audit->record($therapist, $history, AccessAction::Discharged);
    }
}
