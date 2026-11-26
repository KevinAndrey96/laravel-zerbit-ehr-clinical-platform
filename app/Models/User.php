<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $last_name
 * @property string $email
 * @property Role $role
 * @property string $document
 * @property string|null $professional_registry
 * @property string|null $phone
 * @property string|null $signature_path
 * @property bool $active
 */
#[Fillable(['name', 'last_name', 'email', 'password', 'role', 'document', 'professional_registry', 'phone', 'active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'active' => 'boolean',
        ];
    }

    /** @return HasMany<ClinicalHistory, $this> */
    public function clinicalHistories(): HasMany
    {
        return $this->hasMany(ClinicalHistory::class, 'therapist_id');
    }

    /** @return HasMany<Appointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'therapist_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isTherapist(): bool
    {
        return $this->role === Role::Therapist;
    }

    public function fullName(): string
    {
        return $this->name.' '.$this->last_name;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeTherapists(Builder $query): void
    {
        $query->where('role', Role::Therapist->value);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }
}
