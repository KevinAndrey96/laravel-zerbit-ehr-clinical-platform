<?php

namespace Tests\Feature\Clinical;

use App\Enums\AccessAction;
use App\Models\Evolution;
use Closure;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Unit\Domain\SignatureImageTest;

final class EvolutionTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function sessionData(array $overrides = []): array
    {
        return [
            'attended_on' => today()->toDateString(),
            'starts_at' => '08:00',
            'ends_at' => '08:45',
            'pain_scale' => 5,
            'note' => 'Movilización cervical, liberación miofascial y ejercicios isométricos. Buena tolerancia.',
            ...$overrides,
        ];
    }

    public function test_sessions_are_numbered_in_order_by_whoever_attends(): void
    {
        $history = $this->openHistory();
        $colleague = $this->therapist();

        $this->actingAs($history->therapist)->post("/historias/{$history->id}/sesiones", $this->sessionData())
            ->assertRedirect("/historias/{$history->id}#sesion-1");
        $this->actingAs($colleague)->post("/historias/{$history->id}/sesiones", $this->sessionData(['starts_at' => '10:00', 'ends_at' => '10:45']));

        $this->assertSame([1, 2], $history->evolutions()->pluck('session_number')->all());
        $this->assertSame($colleague->id, $history->evolutions()->where('session_number', 2)->value('therapist_id'));
        $this->assertDatabaseHas('access_logs', ['clinical_history_id' => $history->id, 'action' => AccessAction::Evolved->value]);
    }

    public function test_sessions_beyond_the_authorization_are_rejected(): void
    {
        $history = $this->openHistory(authorized: 1);
        Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')->create();

        $this->actingAs($history->therapist)->post("/historias/{$history->id}/sesiones", $this->sessionData())
            ->assertSessionHasErrors(['note' => 'Ya se registraron las 1 sesiones autorizadas para este tratamiento.'], null, 'evolution');
        $this->assertSame(1, $history->evolutions()->count());
    }

    /**
     * @return iterable<string, array{Closure(): array<string, mixed>, string}>
     */
    public static function invalidSessions(): iterable
    {
        yield 'future date' => [fn () => ['attended_on' => today()->addDay()->toDateString()], 'attended_on'];
        yield 'before the history was opened' => [fn () => ['attended_on' => today()->subDays(60)->toDateString()], 'attended_on'];
        yield 'ends before it starts' => [fn () => ['starts_at' => '09:00', 'ends_at' => '08:30'], 'ends_at'];
        yield 'note too short' => [fn () => ['note' => 'Bien.'], 'note'];
        yield 'pain out of scale' => [fn () => ['pain_scale' => 11], 'pain_scale'];
    }

    /**
     * @param  Closure(): array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSessions')]
    public function test_session_data_is_validated(Closure $overrides, string $field): void
    {
        $history = $this->openHistory();

        $this->actingAs($history->therapist)->post("/historias/{$history->id}/sesiones", $this->sessionData($overrides()))
            ->assertSessionHasErrors($field, null, 'evolution');
        $this->assertSame(0, $history->evolutions()->count());
    }

    public function test_session_notes_cannot_be_edited_or_deleted(): void
    {
        $history = $this->openHistory();
        $evolution = Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')->create();
        $this->actingAs($history->therapist);

        $this->put("/historias/{$history->id}/sesiones/{$evolution->id}", $this->sessionData())->assertNotFound();
        $this->patch("/historias/{$history->id}/sesiones/{$evolution->id}", $this->sessionData())->assertNotFound();
        $this->delete("/historias/{$history->id}/sesiones/{$evolution->id}")->assertNotFound();
        $this->assertModelExists($evolution);
    }

    public function test_the_patient_signs_a_session_once_and_the_file_stays_private(): void
    {
        Storage::fake('local');
        $history = $this->openHistory();
        $evolution = Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')->create();
        $url = "/historias/{$history->id}/sesiones/{$evolution->id}/firma";

        $this->actingAs($history->therapist)->post($url, ['signature' => SignatureImageTest::png()])
            ->assertRedirect("/historias/{$history->id}#sesion-1");

        $evolution->refresh();
        $this->assertTrue($evolution->isSigned());
        $this->assertNotNull($evolution->patient_signature_path);
        Storage::disk('local')->assertExists($evolution->patient_signature_path);
        $this->assertStringStartsWith('signatures/', $evolution->patient_signature_path);
        $this->assertDatabaseHas('access_logs', ['clinical_history_id' => $history->id, 'action' => AccessAction::Signed->value]);

        $this->post($url, ['signature' => SignatureImageTest::png()])->assertForbidden();
        $this->assertCount(1, Storage::disk('local')->allFiles('signatures'));
    }

    public function test_forged_signatures_are_rejected(): void
    {
        Storage::fake('local');
        $history = $this->openHistory();
        $evolution = Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')->create();

        $this->actingAs($history->therapist)
            ->post("/historias/{$history->id}/sesiones/{$evolution->id}/firma", ['signature' => 'data:image/png;base64,'.base64_encode('<svg onload=alert(1)>')])
            ->assertSessionHasErrors('signature', null, 'signature');
        $this->assertFalse($evolution->fresh()?->isSigned());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_a_session_cannot_be_signed_through_another_history(): void
    {
        $history = $this->openHistory();
        $other = $this->openHistory($history->therapist);
        $evolution = Evolution::factory()->for($other, 'clinicalHistory')->for($history->therapist, 'therapist')->create();

        $this->actingAs($history->therapist)
            ->post("/historias/{$history->id}/sesiones/{$evolution->id}/firma", ['signature' => SignatureImageTest::png()])
            ->assertNotFound();
    }

    public function test_the_history_page_embeds_signatures_without_public_urls(): void
    {
        Storage::fake('local');
        $history = $this->openHistory();
        $evolution = Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')->create();
        $this->actingAs($history->therapist)->post("/historias/{$history->id}/sesiones/{$evolution->id}/firma", ['signature' => SignatureImageTest::png()]);

        $this->get("/historias/{$history->id}")->assertOk()->assertSee('src="data:image/png;base64,', false)->assertDontSee('/storage/', false);
    }
}
