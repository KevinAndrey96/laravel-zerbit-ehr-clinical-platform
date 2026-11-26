<?php

namespace App\Support;

use App\Enums\AccessAction;
use App\Models\AccessLog;
use App\Models\ClinicalHistory;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Writes the clinical access log required to keep custody of health records.
 */
final readonly class ClinicalAudit
{
    public function __construct(private Request $request) {}

    public function record(User $user, ClinicalHistory $history, AccessAction $action): void
    {
        AccessLog::query()->create([
            'user_id' => $user->id,
            'patient_id' => $history->patient_id,
            'clinical_history_id' => $history->id,
            'action' => $action,
            'ip_address' => $this->request->ip(),
        ]);
    }
}
