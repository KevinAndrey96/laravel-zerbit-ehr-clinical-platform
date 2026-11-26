<?php

namespace Tests;

use App\Models\ClinicalHistory;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function therapist(): User
    {
        return User::factory()->create();
    }

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * Open history with ten authorized sessions, opened twenty days ago.
     */
    protected function openHistory(?User $therapist = null, int $authorized = 10): ClinicalHistory
    {
        return ClinicalHistory::factory()
            ->for(Patient::factory())
            ->for($therapist ?? $this->therapist(), 'therapist')
            ->create(['authorized_sessions' => $authorized]);
    }
}
