<?php

namespace App\Models;

use App\Enums\AccessAction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only trail of who read or wrote a patient's clinical record.
 *
 * @property int $id
 * @property int $user_id
 * @property int $patient_id
 * @property int|null $clinical_history_id
 * @property AccessAction $action
 * @property string|null $ip_address
 * @property CarbonImmutable $created_at
 * @property-read User $user
 * @property-read ClinicalHistory|null $clinicalHistory
 */
#[Fillable(['user_id', 'patient_id', 'clinical_history_id', 'action', 'ip_address'])]
class AccessLog extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'action' => AccessAction::class,
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ClinicalHistory, $this> */
    public function clinicalHistory(): BelongsTo
    {
        return $this->belongsTo(ClinicalHistory::class);
    }
}
