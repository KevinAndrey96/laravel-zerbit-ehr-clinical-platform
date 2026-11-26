<?php

namespace Tests\Feature\Clinical;

use App\Models\Evolution;
use Tests\TestCase;

/**
 * Front-desk staff manage the clinic but never read clinical content, and
 * therapists cannot reach administrative screens.
 */
final class AuthorizationTest extends TestCase
{
    public function test_front_desk_cannot_read_or_write_clinical_histories(): void
    {
        $history = $this->openHistory();
        $evolution = Evolution::factory()->for($history, 'clinicalHistory')->for($history->therapist, 'therapist')->create();
        $this->actingAs($this->admin());

        $this->get('/historias')->assertForbidden();
        $this->get("/historias/{$history->id}")->assertForbidden();
        $this->get("/historias/{$history->id}/pdf")->assertForbidden();
        $this->get("/pacientes/{$history->patient_id}/historias/nueva")->assertForbidden();
        $this->post("/historias/{$history->id}/sesiones", [])->assertForbidden();
        $this->post("/historias/{$history->id}/sesiones/{$evolution->id}/firma", [])->assertForbidden();
        $this->post("/historias/{$history->id}/alta", [])->assertForbidden();
        $this->assertDatabaseCount('access_logs', 0);
    }

    public function test_front_desk_sees_treatments_on_the_patient_file_without_clinical_links(): void
    {
        $history = $this->openHistory();

        $this->actingAs($this->admin())->get("/pacientes/{$history->patient_id}")
            ->assertOk()
            ->assertSee($history->diagnosis_code)
            ->assertDontSee("/historias/{$history->id}", false)
            ->assertSee('El contenido clínico solo es visible para el equipo de fisioterapia.');
    }

    public function test_therapists_cannot_reach_administrative_screens(): void
    {
        $history = $this->openHistory();
        $this->actingAs($history->therapist);

        $this->get('/admin/personal')->assertForbidden();
        $this->get('/admin/aseguradoras')->assertForbidden();
        $this->get('/laboratorio')->assertForbidden();
        $this->get('/citas/nueva')->assertForbidden();
        $this->get("/pacientes/{$history->patient_id}/accesos")->assertForbidden();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $history = $this->openHistory();

        $this->get("/historias/{$history->id}")->assertRedirect('/ingresar');
        $this->get("/historias/{$history->id}/pdf")->assertRedirect('/ingresar');
        $this->get('/laboratorio')->assertRedirect('/ingresar');
    }

    public function test_non_numeric_identifiers_do_not_reach_the_database(): void
    {
        $this->actingAs($this->therapist())->get('/historias/abc')->assertNotFound();
    }
}
