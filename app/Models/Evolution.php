<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\EvolutionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Session note. Clinical records cannot be edited or deleted once written;
 * the only later change allowed is collecting the patient's signature.
 *
 * @property int $id
 * @property int $clinical_history_id
 * @property int $therapist_id
 * @property int $session_number
 * @property CarbonImmutable $attended_on
 * @property string $starts_at
 * @property string $ends_at
 * @property int|null $pain_scale
 * @property string $note
 * @property string|null $patient_signature_path
 * @property CarbonImmutable|null $signed_at
 * @property-read ClinicalHistory $clinicalHistory
 * @property-read User $therapist
 */
#[Fillable(['therapist_id', 'session_number', 'attended_on', 'starts_at', 'ends_at', 'pain_scale', 'note'])]
class Evolution extends Model
{
    /** @use HasFactory<EvolutionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'attended_on' => 'immutable_date',
            'signed_at' => 'immutable_datetime',
            'session_number' => 'integer',
            'pain_scale' => 'integer',
        ];
    }

    /** @return BelongsTo<ClinicalHistory, $this> */
    public function clinicalHistory(): BelongsTo
    {
        return $this->belongsTo(ClinicalHistory::class);
    }

    /** @return BelongsTo<User, $this> */
    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    public function schedule(): string
    {
        return substr($this->starts_at, 0, 5).' a '.substr($this->ends_at, 0, 5);
    }
}
