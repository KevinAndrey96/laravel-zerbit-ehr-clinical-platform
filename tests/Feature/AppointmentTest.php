<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class AppointmentTest extends TestCase
{
    private User $front;

    private User $therapist;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-25 10:00'));
        $this->front = $this->admin();
        $this->therapist = $this->therapist();
        $this->patient = Patient::factory()->create(['email' => 'paciente@correo.test']);
    }

    /**
     * @return TestResponse<Response>
     */
    private function book(string $start, int $duration = 45, ?Patient $patient = null): TestResponse
    {
        return $this->actingAs($this->front)->post('/citas', [
            'patient_id' => ($patient ?? $this->patient)->id,
            'therapist_id' => $this->therapist->id,
            'starts_at' => $start,
            'duration' => $duration,
        ]);
    }

    public function test_front_desk_books_an_appointment(): void
    {
        $this->book('2026-11-26T09:00')->assertRedirect('/agenda?dia=2026-11-26');

        $appointment = Appointment::query()->sole();
        $this->assertSame('2026-11-26 09:45:00', $appointment->ends_at->toDateTimeString());
        $this->assertSame(AppointmentStatus::Scheduled, $appointment->status);
    }

    /**
     * @return iterable<string, array{string, int, bool}>
     */
    public static function slots(): iterable
    {
        yield 'same time' => ['2026-11-26T09:00', 45, false];
        yield 'overlapping start' => ['2026-11-26T09:30', 30, false];
        yield 'wrapping around' => ['2026-11-26T08:30', 60, false];
        yield 'right before' => ['2026-11-26T08:15', 45, true];
        yield 'right after' => ['2026-11-26T09:45', 45, true];
    }

    #[DataProvider('slots')]
    public function test_the_therapist_is_never_double_booked(string $start, int $duration, bool $accepted): void
    {
        $this->book('2026-11-26T09:00');

        $response = $this->book($start, $duration, Patient::factory()->create());

        $accepted ? $response->assertSessionHasNoErrors() : $response->assertSessionHasErrors(['starts_at' => 'El fisioterapeuta ya tiene una cita en ese horario.']);
        $this->assertSame($accepted ? 2 : 1, Appointment::query()->count());
    }

    public function test_the_patient_is_never_double_booked(): void
    {
        $this->book('2026-11-26T09:00');
        $other = $this->therapist();

        $this->actingAs($this->front)->post('/citas', [
            'patient_id' => $this->patient->id, 'therapist_id' => $other->id, 'starts_at' => '2026-11-26T09:15', 'duration' => 30,
        ])->assertSessionHasErrors(['starts_at' => 'El paciente ya tiene una cita en ese horario.']);
    }

    public function test_cancelled_appointments_free_the_slot(): void
    {
        $this->book('2026-11-26T09:00');
        Appointment::query()->sole()->forceFill(['status' => AppointmentStatus::Cancelled])->save();

        $this->book('2026-11-26T09:00', 45, Patient::factory()->create())->assertSessionHasNoErrors();
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function outsideHours(): iterable
    {
        yield 'in the past' => ['2026-11-25T08:00', 45];
        yield 'too early' => ['2026-11-26T05:30', 45];
        yield 'ends after closing' => ['2026-11-26T19:30', 45];
        yield 'on sunday' => ['2026-11-29T10:00', 45];
        yield 'odd duration' => ['2026-11-26T10:00', 20];
    }

    #[DataProvider('outsideHours')]
    public function test_only_future_slots_within_opening_hours_are_bookable(string $start, int $duration): void
    {
        $this->book($start, $duration)->assertSessionHasErrors();
        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_inactive_therapists_cannot_be_booked(): void
    {
        $this->therapist->update(['active' => false]);

        $this->book('2026-11-26T09:00')->assertSessionHasErrors('therapist_id');
    }

    public function test_status_moves_forward_and_cancelling_needs_a_reason(): void
    {
        $appointment = Appointment::factory()->for($this->patient)->for($this->therapist, 'therapist')->create();
        $url = "/citas/{$appointment->id}/estado";
        $this->actingAs($this->front);

        $this->patch($url, ['status' => 'cancelada'])->assertSessionHasErrors('cancellation_reason');
        $this->patch($url, ['status' => 'confirmada'])->assertSessionHasNoErrors();
        $this->patch($url, ['status' => 'programada'])->assertSessionHasErrors('status');
        $this->patch($url, ['status' => 'atendida'])->assertSessionHasErrors(['status' => 'La asistencia se registra cuando llega la hora de la cita.']);
        $this->patch($url, ['status' => 'cancelada', 'cancellation_reason' => 'Incapacidad médica'])->assertSessionHasNoErrors();

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::Cancelled, $appointment->status);
        $this->assertSame('Incapacidad médica', $appointment->cancellation_reason);
    }

    public function test_therapists_record_attendance_only_on_their_own_agenda(): void
    {
        $appointment = Appointment::factory()->for($this->patient)->for($this->therapist, 'therapist')
            ->create(['starts_at' => now()->subHour(), 'ends_at' => now()->subMinutes(15)]);

        $this->actingAs($this->therapist())->patch("/citas/{$appointment->id}/estado", ['status' => 'atendida'])->assertForbidden();
        $this->actingAs($this->therapist)->patch("/citas/{$appointment->id}/estado", ['status' => 'atendida'])->assertSessionHasNoErrors();
        $this->assertSame(AppointmentStatus::Attended, $appointment->fresh()?->status);
    }

    public function test_therapists_only_see_their_own_agenda(): void
    {
        $mine = Appointment::factory()->for($this->therapist, 'therapist')->create(['starts_at' => '2026-11-26 09:00', 'ends_at' => '2026-11-26 09:45']);
        $theirs = Appointment::factory()->create(['starts_at' => '2026-11-26 11:00', 'ends_at' => '2026-11-26 11:45']);

        $this->actingAs($this->therapist)->get('/agenda?dia=2026-11-26&fisioterapeuta='.$theirs->therapist_id)
            ->assertOk()
            ->assertSee($mine->patient->fullName())
            ->assertDontSee($theirs->patient->fullName());
    }

    public function test_reminders_are_emailed_the_day_before(): void
    {
        Mail::fake();
        $tomorrow = Appointment::factory()->for($this->patient)->for($this->therapist, 'therapist')->create();
        $cancelled = Appointment::factory()->for($this->therapist, 'therapist')->create(['status' => AppointmentStatus::Cancelled, 'starts_at' => '2026-11-26 15:00', 'ends_at' => '2026-11-26 15:45']);
        $noEmail = Appointment::factory()->for(Patient::factory()->state(['email' => null]))->for($this->therapist, 'therapist')
            ->create(['starts_at' => '2026-11-26 16:00', 'ends_at' => '2026-11-26 16:45']);
        Appointment::factory()->for($this->therapist, 'therapist')->create(['starts_at' => '2026-11-28 09:00', 'ends_at' => '2026-11-28 09:45']);

        $this->assertSame(0, Artisan::call('appointments:send-reminders'));
        $this->assertStringContainsString('Recordatorios enviados: 1', Artisan::output());
        Artisan::call('appointments:send-reminders');
        $this->assertStringContainsString('Recordatorios enviados: 0', Artisan::output());

        Mail::assertSent(AppointmentReminder::class, 1);
        Mail::assertSent(AppointmentReminder::class, fn (AppointmentReminder $mail) => $mail->hasTo('paciente@correo.test') && $mail->appointment->is($tomorrow));
        $this->assertNotNull($tomorrow->fresh()?->reminder_sent_at);
        $this->assertNull($cancelled->fresh()?->reminder_sent_at);
        $this->assertNull($noEmail->fresh()?->reminder_sent_at);
    }

    public function test_front_desk_can_send_a_reminder_by_hand(): void
    {
        Mail::fake();
        $appointment = Appointment::factory()->for($this->patient)->for($this->therapist, 'therapist')->create();

        $this->actingAs($this->front)->post("/citas/{$appointment->id}/recordatorio")->assertSessionHasNoErrors();
        $this->actingAs($this->therapist)->post("/citas/{$appointment->id}/recordatorio")->assertForbidden();

        Mail::assertSent(AppointmentReminder::class, 1);
    }

    public function test_the_reminder_mail_renders_the_appointment(): void
    {
        $appointment = Appointment::factory()->for($this->patient)->for($this->therapist, 'therapist')->create();

        (new AppointmentReminder($appointment))
            ->assertSeeInHtml($this->patient->first_name)
            ->assertSeeInHtml('9:00 a. m.')
            ->assertSeeInHtml($this->therapist->fullName());
    }
}
