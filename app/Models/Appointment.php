<?php

namespace App\Models;

use App\Domain\Scheduling\TimeSlot;
use App\Enums\AppointmentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $patient_id
 * @property int $therapist_id
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property AppointmentStatus $status
 * @property string|null $notes
 * @property string|null $cancellation_reason
 * @property CarbonImmutable|null $reminder_sent_at
 * @property-read Patient $patient
 * @property-read User $therapist
 */
#[Fillable(['patient_id', 'therapist_id', 'starts_at', 'ends_at', 'status', 'notes'])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'status' => AppointmentStatus::class,
            'reminder_sent_at' => 'immutable_datetime',
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

    public function slot(): TimeSlot
    {
        return new TimeSlot($this->starts_at, $this->ends_at);
    }

    /**
     * Appointments that still hold time on the agenda.
     *
     * @param  Builder<self>  $query
     */
    public function scopeHoldingSlot(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::slotValues());
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOn(Builder $query, CarbonImmutable $day): void
    {
        $query->whereBetween('starts_at', [$day->startOfDay(), $day->endOfDay()]);
    }
}
