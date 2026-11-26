<?php

namespace App\Policies;

use App\Models\Evolution;
use App\Models\User;

/**
 * Session notes are append-only; the only later change is the patient's
 * signature, captured once.
 */
final class EvolutionPolicy
{
    public function sign(User $user, Evolution $evolution): bool
    {
        return $user->isTherapist() && ! $evolution->isSigned();
    }
}
