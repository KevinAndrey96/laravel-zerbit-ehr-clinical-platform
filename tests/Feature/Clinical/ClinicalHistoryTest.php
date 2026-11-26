<?php

namespace Tests\Feature\Clinical;

use App\Enums\AccessAction;
use App\Models\ClinicalHistory;
use App\Models\Evolution;
use App\Models\Patient;
use Tests\TestCase;

final class ClinicalHistoryTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'opened_on' => today()->toDateString(),
            'reason' => 'Dolor cervical después de un accidente de tránsito.',
            'diagnosis_code' => 's134',
            'diagnosis' => 'Esguince de la columna cervical',
            'physio_diagnosis' => 'Limitación de la rotación cervical por dolor y espasmo.',
            'objective' => 'Recuperar la movilidad cervical sin dolor.',
            'authorized_sessions' => 10,
            'copayment_cop' => '4.800',
            'pain_scale' => 8,
            'background_pathological' => 'Hipertensión controlada',
            ...$overrides,
        ];
    }

    public function test_a_therapist_opens_a_history_with_a_normalized_diagnosis(): void
    {
        $therapist = $this->therapist();
        $patient = Patient::factory()->create();

        $response = $this->actingAs($therapist)->post("/pacientes/{$patient->id}/historias", $this->payload());

        $history = ClinicalHistory::query()->sole();
        $response->assertRedirect("/historias/{$history->id}");
        $this->assertSame('S13.4', $history->diagnosis_code);
        $this->assertSame(4800, $history->copayment_cop);
        $this->assertSame($therapist->id, $history->therapist_id);
        $this->assertDatabaseHas('access_logs', ['clinical_history_id' => $history->id, 'action' => AccessAction::Opened->value]);
    }

    public function test_invalid_diagnosis_codes_are_rejected(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->therapist())->post("/pacientes/{$patient->id}/historias", $this->payload(['diagnosis_code' => 'dolor']))
            ->assertSessionHasErrors('diagnosis_code');
        $this->assertDatabaseCount('clinical_histories', 0);
    }

    public function test_a_patient_has_a_single_open_history(): void
    {
        $history = $this->openHistory();

        $this->actingAs($this->therapist())->post("/pacientes/{$history->patient_id}/historias", $this->payload())
            ->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('clinical_histories', 1);
    }

    public function test_viewing_and_printing_are_logged(): void
    {
        $history = $this->openHistory();
        $colleague = $this->therapist();

        $this->actingAs($colleague)->get("/historias/{$history->id}")->assertOk()->assertSee($history->reason);
        $pdf = $this->actingAs($colleague)->get("/historias/{$history->id}/pdf");

        $pdf->assertOk()->assertDownload("historia-clinica-{$history->id}.pdf");
        $this->assertStringStartsWith('%PDF', (string) $pdf->getContent());
        $actions = $history->accessLogs()->where('user_id', $colleague->id)->pluck('action')->map->value->all();
        $this->assertSame([AccessAction::Viewed->value, AccessAction::Downloaded->value], $actions);
    }

    public function test_only_the_treating_therapist_edits_and_only_before_the_first_session(): void
    {
        $history = $this->openHistory();
        $payload = $this->payload(['opened_on' => $history->opened_on->toDateString(), 'objective' => 'Objetivo corregido para el tratamiento.']);

        $this->actingAs($this->therapist())->put("/historias/{$history->id}", $payload)->assertForbidden();
        $this->actingAs($history->therapist)->put("/historias/{$history->id}", $payload)->assertRedirect("/historias/{$history->id}");
        $this->assertSame('Objetivo corregido para el tratamiento.', $history->fresh()?->objective);

        Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')->create();
        $this->actingAs($history->therapist)->put("/historias/{$history->id}", $payload)->assertForbidden();
    }

    public function test_discharge_closes_the_history(): void
    {
        $history = $this->openHistory();

        $this->actingAs($history->therapist)->post("/historias/{$history->id}/alta", [
            'closed_on' => today()->toDateString(),
            'discharge_summary' => 'Paciente sin dolor, recupera la movilidad completa.',
        ])->assertRedirect("/historias/{$history->id}");

        $history->refresh();
        $this->assertFalse($history->isOpen());
        $this->assertDatabaseHas('access_logs', ['clinical_history_id' => $history->id, 'action' => AccessAction::Discharged->value]);
        $this->actingAs($history->therapist)->post("/historias/{$history->id}/sesiones", [])->assertForbidden();
        $this->actingAs($history->therapist)->post("/historias/{$history->id}/alta", [])->assertForbidden();
    }

    public function test_discharge_cannot_predate_the_last_session(): void
    {
        $history = $this->openHistory();
        Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')
            ->create(['attended_on' => today()->subDay()->toDateString()]);

        $this->actingAs($history->therapist)->post("/historias/{$history->id}/alta", [
            'closed_on' => today()->subDays(3)->toDateString(),
            'discharge_summary' => 'Paciente sin dolor, recupera la movilidad completa.',
        ])->assertSessionHasErrors('closed_on', null, 'discharge');
        $this->assertTrue($history->fresh()?->isOpen());
    }

    public function test_only_the_treating_therapist_discharges(): void
    {
        $history = $this->openHistory();

        $this->actingAs($this->therapist())->post("/historias/{$history->id}/alta", [
            'closed_on' => today()->toDateString(),
            'discharge_summary' => 'Paciente sin dolor, recupera la movilidad completa.',
        ])->assertForbidden();
    }
}
