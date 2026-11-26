<?php

namespace Tests\Feature;

use App\Enums\DeliveryChannel;
use App\Enums\LabStatus;
use App\Mail\LabResultReady;
use App\Models\LabSample;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class LabSampleTest extends TestCase
{
    private User $front;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        $this->front = $this->admin();
    }

    public function test_registering_a_sample_assigns_a_sequential_code(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->front)->post('/laboratorio', [
            'patient_id' => $patient->id,
            'sample_type' => 'sangre',
            'exam' => 'Hemoglobina glicosilada',
            'taken_on' => today()->toDateString(),
            'notify_by_email' => '1',
        ])->assertRedirect();

        $sample = LabSample::query()->sole();
        $this->assertSame(sprintf('LAB-%s-%06d', today()->year, $sample->id), $sample->code);
        $this->assertSame(LabStatus::Registered, $sample->status);
        $this->assertSame($this->front->id, $sample->registered_by);
    }

    public function test_the_full_workflow_notifies_the_patient_without_attaching_the_result(): void
    {
        $sample = LabSample::factory()->for(Patient::factory()->state(['email' => 'paciente@correo.test']))->create();
        $this->actingAs($this->front);

        $this->post("/laboratorio/{$sample->id}/analisis")->assertSessionHasNoErrors();
        $this->post("/laboratorio/{$sample->id}/resultado", ['result' => UploadedFile::fake()->create('resultado.pdf', 120, 'application/pdf')])
            ->assertSessionHasNoErrors();

        $sample->refresh();
        $this->assertSame(LabStatus::ResultReady, $sample->status);
        $this->assertNotNull($sample->result_path);
        Storage::disk('local')->assertExists($sample->result_path);
        Mail::assertSent(LabResultReady::class, fn (LabResultReady $mail) => $mail->hasTo('paciente@correo.test') && $mail->attachments === [] && $mail->rawAttachments === []);

        $this->get("/laboratorio/{$sample->id}/resultado")->assertOk()->assertDownload("resultado-{$sample->code}.pdf");
        $this->post("/laboratorio/{$sample->id}/entrega")->assertSessionHasNoErrors();

        $sample->refresh();
        $this->assertSame(LabStatus::Delivered, $sample->status);
        $this->assertSame(DeliveryChannel::InPerson, $sample->delivery_channel);
    }

    public function test_steps_cannot_be_skipped(): void
    {
        $sample = LabSample::factory()->create();
        $this->actingAs($this->front);

        $this->post("/laboratorio/{$sample->id}/entrega")->assertSessionHasErrors('status');
        $this->post("/laboratorio/{$sample->id}/resultado", ['result' => UploadedFile::fake()->create('r.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('status');
        $this->assertSame(LabStatus::Registered, $sample->fresh()?->status);
        Mail::assertNothingSent();
    }

    public function test_only_pdf_results_are_accepted(): void
    {
        $sample = LabSample::factory()->create(['status' => LabStatus::InAnalysis]);

        $this->actingAs($this->front)->post("/laboratorio/{$sample->id}/resultado", ['result' => UploadedFile::fake()->create('shell.php', 2, 'text/x-php')])
            ->assertSessionHasErrors('result');
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_replacing_a_result_removes_the_previous_file(): void
    {
        $sample = LabSample::factory()->create(['status' => LabStatus::InAnalysis]);
        $this->actingAs($this->front);

        $this->post("/laboratorio/{$sample->id}/resultado", ['result' => UploadedFile::fake()->create('v1.pdf', 10, 'application/pdf')]);
        $first = $sample->fresh()?->result_path;
        $this->post("/laboratorio/{$sample->id}/resultado", ['result' => UploadedFile::fake()->create('v2.pdf', 10, 'application/pdf')]);

        $this->assertIsString($first);
        Storage::disk('local')->assertMissing($first);
        $this->assertCount(1, Storage::disk('local')->allFiles('lab-results'));
    }

    public function test_patients_without_email_are_not_notified(): void
    {
        $sample = LabSample::factory()->for(Patient::factory()->state(['email' => null]))->create(['status' => LabStatus::InAnalysis]);

        $this->actingAs($this->front)->post("/laboratorio/{$sample->id}/resultado", ['result' => UploadedFile::fake()->create('r.pdf', 10, 'application/pdf')]);

        Mail::assertNothingSent();
        $this->assertSame(LabStatus::ResultReady, $sample->fresh()?->status);
    }

    public function test_the_pending_list_hides_delivered_samples(): void
    {
        $pending = LabSample::factory()->create(['exam' => 'Glicemia basal']);
        $delivered = LabSample::factory()->create(['exam' => 'Uroanálisis completo', 'status' => LabStatus::Delivered]);

        $this->actingAs($this->front)->get('/laboratorio')->assertSee($pending->code)->assertDontSee($delivered->code);
        $this->actingAs($this->front)->get('/laboratorio?estado=entregado')->assertSee($delivered->code)->assertDontSee($pending->code);
    }
}
