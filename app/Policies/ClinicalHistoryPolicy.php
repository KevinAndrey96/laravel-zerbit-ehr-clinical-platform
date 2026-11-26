<?php

namespace App\Policies;

use App\Models\ClinicalHistory;
use App\Models\User;

/**
 * Clinical content is reserved to the care team: any active therapist can
 * read and attend a patient, only the treating therapist edits or discharges,
 * and front-desk staff never read it.
 */
final class ClinicalHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTherapist();
    }

    public function view(User $user, ClinicalHistory $history): bool
    {
        return $user->isTherapist();
    }

    public function create(User $user): bool
    {
        return $user->isTherapist();
    }

    /** The opening assessment can be corrected only before the first session note. */
    public function update(User $user, ClinicalHistory $history): bool
    {
        return $user->id === $history->therapist_id && $history->isOpen() && $history->heldSessions() === 0;
    }

    public function evolve(User $user, ClinicalHistory $history): bool
    {
        return $user->isTherapist() && $history->isOpen();
    }

    public function discharge(User $user, ClinicalHistory $history): bool
    {
        return $user->id === $history->therapist_id && $history->isOpen();
    }
}
