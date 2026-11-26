<?php

namespace App\Models;

use App\Domain\Clinical\SessionPlan;
use App\Enums\FallRisk;
use Carbon\CarbonImmutable;
use Database\Factories\ClinicalHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Physiotherapy clinical history for one treatment episode: reason,
 * diagnosis, background, initial assessment and the session notes.
 *
 * @property int $id
 * @property int $patient_id
 * @property int $therapist_id
 * @property CarbonImmutable $opened_on
 * @property string $reason
 * @property string $diagnosis_code
 * @property string $diagnosis
 * @property string $physio_diagnosis
 * @property string $objective
 * @property int $authorized_sessions
 * @property string|null $authorization_number
 * @property int|null $copayment_cop
 * @property int|null $pain_scale
 * @property string|null $pain_notes
 * @property FallRisk|null $fall_risk
 * @property CarbonImmutable|null $closed_on
 * @property string|null $discharge_summary
 * @property int|null $evolutions_count
 * @property-read Patient $patient
 * @property-read User $therapist
 */
#[Fillable([
    'patient_id', 'therapist_id', 'opened_on', 'reason', 'diagnosis_code', 'diagnosis', 'physio_diagnosis', 'objective',
    'authorized_sessions', 'authorization_number', 'copayment_cop',
    'background_pathological', 'background_surgical', 'background_traumatic', 'background_allergic',
    'background_family', 'background_pharmacological', 'background_other',
    'pain_scale', 'pain_notes', 'edema', 'joint_mobility', 'sensitivity', 'skin', 'muscle_strength',
    'flexibility', 'posture', 'gait', 'balance', 'fall_risk', 'other_findings',
])]
class ClinicalHistory extends Model
{
    /** @use HasFactory<ClinicalHistoryFactory> */
    use HasFactory;

    /** @var array<string, string> */
    public const array BACKGROUND = [
        'background_pathological' => 'Patológicos',
        'background_surgical' => 'Quirúrgicos',
        'background_traumatic' => 'Traumáticos',
        'background_allergic' => 'Alérgicos',
        'background_family' => 'Familiares',
        'background_pharmacological' => 'Farmacológicos',
        'background_other' => 'Otros',
    ];

    /** @var array<string, string> */
    public const array ASSESSMENT = [
        'edema' => 'Edema',
        'joint_mobility' => 'Movilidad articular',
        'sensitivity' => 'Sensibilidad',
        'skin' => 'Sistema tegumentario',
        'muscle_strength' => 'Fuerza muscular',
        'flexibility' => 'Flexibilidad',
        'posture' => 'Postura',
        'gait' => 'Marcha',
        'balance' => 'Equilibrio',
        'other_findings' => 'Otros hallazgos',
    ];

    protected function casts(): array
    {
        return [
            'opened_on' => 'immutable_date',
            'closed_on' => 'immutable_date',
            'authorized_sessions' => 'integer',
            'copayment_cop' => 'integer',
            'pain_scale' => 'integer',
            'fall_risk' => FallRisk::class,
        ];
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return BelongsTo<User, $this> */
    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /** @return HasMany<Evolution, $this> */
    public function evolutions(): HasMany
    {
        return $this->hasMany(Evolution::class)->orderBy('session_number');
    }

    /** @return HasMany<AccessLog, $this> */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }

    public function isOpen(): bool
    {
        return $this->closed_on === null;
    }

    public function heldSessions(): int
    {
        return $this->evolutions_count ?? $this->evolutions()->count();
    }

    public function plan(): SessionPlan
    {
        return new SessionPlan($this->authorized_sessions, $this->heldSessions());
    }

    public function text(string $field): ?string
    {
        $value = $this->getAttribute($field);

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('closed_on');
    }
}
