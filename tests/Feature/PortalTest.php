<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\DeliveryChannel;
use App\Enums\LabStatus;
use App\Mail\PortalAccessLink;
use App\Models\Appointment;
use App\Models\LabSample;
use App\Models\Patient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PortalTest extends TestCase
{
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
        $this->patient = Patient::factory()->create(['document_number' => '1012345678', 'email' => 'laura@paciente.test']);
    }

    private function enter(?Patient $patient = null): void
    {
        $url = URL::temporarySignedRoute('portal.enter', now()->addMinutes(20), ['patient' => ($patient ?? $this->patient)->id]);
        $this->get($url)->assertRedirect('/portal/inicio');
    }

    private function readySample(Patient $patient): LabSample
    {
        Storage::disk('local')->put('lab-results/demo.pdf', '%PDF-1.4 demo');

        return LabSample::factory()->for($patient)->create([
            'status' => LabStatus::ResultReady, 'result_path' => 'lab-results/demo.pdf', 'result_ready_at' => now(),
        ]);
    }

    public function test_a_matching_patient_receives_a_signed_link(): void
    {
        $this->post('/portal/enlace', ['document_type' => 'CC', 'document_number' => '1.012.345.678', 'email' => 'LAURA@paciente.test'])
            ->assertRedirect('/portal')
            ->assertSessionHas('status');

        Mail::assertSent(PortalAccessLink::class, function (PortalAccessLink $mail): bool {
            return $mail->hasTo('laura@paciente.test') && str_contains($mail->url, '/portal/entrar/'.$this->patient->id) && str_contains($mail->url, 'signature=');
        });
    }

    /**
     * @return iterable<string, array{array<string, string>}>
     */
    public static function mismatches(): iterable
    {
        yield 'unknown document' => [['document_type' => 'CC', 'document_number' => '999999', 'email' => 'laura@paciente.test']];
        yield 'wrong email' => [['document_type' => 'CC', 'document_number' => '1012345678', 'email' => 'otro@correo.test']];
        yield 'wrong document type' => [['document_type' => 'CE', 'document_number' => '1012345678', 'email' => 'laura@paciente.test']];
    }

    /**
     * @param  array<string, string>  $data
     */
    #[DataProvider('mismatches')]
    public function test_the_answer_never_reveals_whether_someone_is_a_patient(array $data): void
    {
        $this->post('/portal/enlace', ['document_type' => 'CC', 'document_number' => '1012345678', 'email' => 'laura@paciente.test']);
        $matching = session('status');
        $this->post('/portal/enlace', $data);

        $this->assertIsString($matching);
        $this->assertSame($matching, session('status'));
        Mail::assertSent(PortalAccessLink::class, 1);
    }

    public function test_link_requests_are_throttled(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post('/portal/enlace', ['document_type' => 'CC', 'document_number' => '1012345678', 'email' => 'laura@paciente.test']);
        }

        $this->post('/portal/enlace', ['document_type' => 'CC', 'document_number' => '1012345678', 'email' => 'laura@paciente.test'])
            ->assertTooManyRequests();
    }

    public function test_tampered_or_expired_links_are_rejected(): void
    {
        $url = URL::temporarySignedRoute('portal.enter', now()->addMinutes(20), ['patient' => $this->patient->id]);
        $other = Patient::factory()->create();

        $this->get(str_replace('/entrar/'.$this->patient->id, '/entrar/'.$other->id, $url))->assertForbidden();
        $this->travel(21)->minutes();
        $this->get($url)->assertForbidden();
        $this->get('/portal/inicio')->assertRedirect('/portal');
    }

    public function test_the_portal_lists_only_the_patients_own_results_and_appointments(): void
    {
        $mine = $this->readySample($this->patient);
        $theirs = $this->readySample(Patient::factory()->create());
        $pending = LabSample::factory()->for($this->patient)->create(['exam' => 'Coprológico seriado']);
        Appointment::factory()->for($this->patient)->create(['starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addMinutes(45)]);
        Appointment::factory()->for($this->patient)->create(['status' => AppointmentStatus::Cancelled, 'notes' => 'cancelada-no-mostrar']);

        $this->enter();

        $this->get('/portal/inicio')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee($mine->code)
            ->assertDontSee($theirs->code)
            ->assertSee($pending->exam)
            ->assertSee('Próximas citas');
    }

    public function test_downloading_marks_the_result_as_delivered_through_the_portal(): void
    {
        $sample = $this->readySample($this->patient);
        $this->enter();

        $this->get("/portal/resultados/{$sample->id}")->assertOk()->assertDownload("resultado-{$sample->code}.pdf");

        $sample->refresh();
        $this->assertSame(LabStatus::Delivered, $sample->status);
        $this->assertSame(DeliveryChannel::Portal, $sample->delivery_channel);
        $this->get("/portal/resultados/{$sample->id}")->assertOk();
    }

    public function test_patients_cannot_download_someone_elses_result(): void
    {
        $theirs = $this->readySample(Patient::factory()->create());
        $notReady = LabSample::factory()->for($this->patient)->create();
        $this->enter();

        $this->get("/portal/resultados/{$theirs->id}")->assertNotFound();
        $this->get("/portal/resultados/{$notReady->id}")->assertNotFound();
        $this->assertSame(LabStatus::ResultReady, $theirs->fresh()?->status);
    }

    public function test_the_session_expires_after_thirty_idle_minutes(): void
    {
        $this->enter();
        $this->travel(20)->minutes();
        $this->get('/portal/inicio')->assertOk();
        $this->travel(31)->minutes();

        $this->get('/portal/inicio')->assertRedirect('/portal');
    }

    public function test_signing_out_closes_the_portal_session(): void
    {
        $this->enter();

        $this->post('/portal/salir')->assertRedirect('/portal');
        $this->get('/portal/inicio')->assertRedirect('/portal');
    }

    public function test_staff_sessions_do_not_open_the_portal(): void
    {
        $this->actingAs($this->admin())->get('/portal/inicio')->assertRedirect('/portal');
    }
}
