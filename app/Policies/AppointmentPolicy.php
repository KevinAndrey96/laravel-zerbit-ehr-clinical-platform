<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * The front desk books and cancels; therapists see their own agenda and
 * record whether the patient came.
 */
final class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || $user->id === $appointment->therapist_id;
    }

    public function remind(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin();
    }
}
