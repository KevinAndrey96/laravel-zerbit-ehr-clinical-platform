<?php

namespace App\Models;

use App\Domain\Clinical\Age;
use App\Enums\CivilStatus;
use App\Enums\DocumentType;
use App\Enums\Sex;
use Carbon\CarbonImmutable;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property DocumentType $document_type
 * @property string $document_number
 * @property string $first_name
 * @property string|null $second_name
 * @property string $first_surname
 * @property string|null $second_surname
 * @property CarbonImmutable $birthdate
 * @property Sex $sex
 * @property CivilStatus|null $civil_status
 * @property int|null $insurer_id
 * @property string|null $occupation
 * @property string $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $city
 * @property-read Insurer|null $insurer
 */
#[Fillable(['document_type', 'document_number', 'first_name', 'second_name', 'first_surname', 'second_surname', 'birthdate', 'sex', 'civil_status', 'insurer_id', 'occupation', 'phone', 'email', 'address', 'city'])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'birthdate' => 'immutable_date',
            'sex' => Sex::class,
            'civil_status' => CivilStatus::class,
        ];
    }

    /** @return BelongsTo<Insurer, $this> */
    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class);
    }

    /** @return HasMany<ClinicalHistory, $this> */
    public function clinicalHistories(): HasMany
    {
        return $this->hasMany(ClinicalHistory::class);
    }

    /** @return HasMany<LabSample, $this> */
    public function labSamples(): HasMany
    {
        return $this->hasMany(LabSample::class);
    }

    /** @return HasMany<Appointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function fullName(): string
    {
        return implode(' ', array_filter([$this->first_name, $this->second_name, $this->first_surname, $this->second_surname]));
    }

    public function document(): string
    {
        return $this->document_type->value.' '.$this->document_number;
    }

    public function age(?CarbonImmutable $on = null): Age
    {
        return Age::between($this->birthdate, $on ?? CarbonImmutable::today());
    }

    /**
     * Matches names, surnames or document number, ignoring case.
     *
     * @param  Builder<self>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);
        if ($term === '') {
            return;
        }
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_strtolower($term)).'%';
        $query->where(function (Builder $query) use ($like): void {
            $query->whereRaw('lower(first_name) like ?', [$like])
                ->orWhereRaw('lower(first_surname) like ?', [$like])
                ->orWhereRaw('lower(second_surname) like ?', [$like])
                ->orWhere('document_number', 'like', $like);
        });
    }
}
