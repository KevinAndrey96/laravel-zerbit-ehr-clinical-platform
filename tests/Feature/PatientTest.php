<?php

namespace Tests\Feature;

use App\Enums\AccessAction;
use App\Models\Insurer;
use App\Models\Patient;
use App\Support\ClinicalAudit;
use Tests\TestCase;

final class PatientTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'document_type' => 'CC',
            'document_number' => '1.023.456.789',
            'first_name' => 'Valeria',
            'first_surname' => 'Quintero',
            'birthdate' => '1994-05-20',
            'sex' => 'F',
            'phone' => '3104567890',
            'email' => ' Valeria@Correo.test ',
            'insurer_id' => Insurer::factory()->create()->id,
            ...$overrides,
        ];
    }

    public function test_both_roles_register_patients_with_a_normalized_document(): void
    {
        foreach ([$this->admin(), $this->therapist()] as $i => $user) {
            $this->actingAs($user)->post('/pacientes', $this->payload(['document_number' => '1.023.456.78'.$i]))->assertRedirect();
        }

        $this->assertSame(['102345678'.'0', '102345678'.'1'], Patient::query()->orderBy('id')->pluck('document_number')->all());
        $this->assertSame('valeria@correo.test', Patient::query()->value('email'));
    }

    public function test_documents_are_unique_per_type(): void
    {
        $this->actingAs($this->admin());
        $this->post('/pacientes', $this->payload())->assertSessionHasNoErrors();

        $this->post('/pacientes', $this->payload())->assertSessionHasErrors(['document_number' => 'Ya existe un paciente con ese documento.']);
        $this->post('/pacientes', $this->payload(['document_type' => 'CE']))->assertSessionHasNoErrors();
    }

    public function test_inactive_insurers_cannot_be_assigned(): void
    {
        $inactive = Insurer::factory()->create(['active' => false]);

        $this->actingAs($this->admin())->post('/pacientes', $this->payload(['insurer_id' => $inactive->id]))->assertSessionHasErrors('insurer_id');
    }

    public function test_search_matches_names_and_documents(): void
    {
        $valeria = Patient::factory()->create(['first_name' => 'Valeria', 'first_surname' => 'Quintero', 'document_number' => '52111222']);
        $other = Patient::factory()->create(['first_name' => 'Tomás', 'first_surname' => 'Arango']);
        $this->actingAs($this->therapist());

        $this->get('/pacientes?q=quinter')->assertSee($valeria->fullName())->assertDontSee($other->fullName());
        $this->get('/pacientes?q=52111')->assertSee($valeria->fullName())->assertDontSee($other->fullName());
    }

    public function test_front_desk_reviews_who_accessed_a_clinical_history(): void
    {
        $history = $this->openHistory();
        $colleague = $this->therapist();
        $this->actingAs($colleague)->get("/historias/{$history->id}");
        app(ClinicalAudit::class)->record($colleague, $history, AccessAction::Downloaded);

        $this->actingAs($this->admin())->get("/pacientes/{$history->patient_id}/accesos")
            ->assertOk()
            ->assertSee($colleague->fullName())
            ->assertSee(AccessAction::Viewed->label())
            ->assertSee(AccessAction::Downloaded->label());
    }
}
