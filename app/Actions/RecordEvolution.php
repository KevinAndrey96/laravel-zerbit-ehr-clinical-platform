<?php

namespace App\Actions;

use App\Enums\AccessAction;
use App\Models\ClinicalHistory;
use App\Models\Evolution;
use App\Models\User;
use App\Support\ClinicalAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Writes the next session note. Sessions are numbered in order and cannot
 * exceed what the insurer authorized.
 */
final readonly class RecordEvolution
{
    public function __construct(private ClinicalAudit $audit) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ClinicalHistory $history, User $therapist, array $data): Evolution
    {
        $evolution = DB::transaction(function () use ($history, $therapist, $data): Evolution {
            $locked = ClinicalHistory::query()->whereKey($history->id)->lockForUpdate()->firstOrFail();
            $plan = $locked->plan();
            if ($plan->isExhausted()) {
                throw ValidationException::withMessages([
                    'note' => 'Ya se registraron las '.$plan->authorized.' sesiones autorizadas para este tratamiento.',
                ])->errorBag('evolution');
            }

            return $locked->evolutions()->create([
                ...$data,
                'therapist_id' => $therapist->id,
                'session_number' => $plan->nextSessionNumber(),
            ]);
        });
        $this->audit->record($therapist, $history, AccessAction::Evolved);

        return $evolution;
    }
}
