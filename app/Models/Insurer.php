<?php

namespace App\Models;

use App\Domain\Tax\Nit;
use Database\Factories\InsurerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Health insurer (EPS, prepaid plan or ARL) that authorizes treatments.
 *
 * @property int $id
 * @property string $name
 * @property string $nit
 * @property bool $active
 */
#[Fillable(['name', 'nit', 'active'])]
class Insurer extends Model
{
    /** @use HasFactory<InsurerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @return HasMany<Patient, $this> */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function formattedNit(): string
    {
        return Nit::parse($this->nit)->formatted();
    }
}
