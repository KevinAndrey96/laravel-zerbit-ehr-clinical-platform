<?php

namespace App\Actions;

use App\Enums\DeliveryChannel;
use App\Enums\LabStatus;
use App\Mail\LabResultReady;
use App\Models\LabSample;
use App\Models\Patient;
use App\Models\User;
use App\Support\PrivateFiles;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Lab sample lifecycle: register, analyse, attach the result and deliver it.
 */
final readonly class LabWorkflow
{
    public function __construct(private PrivateFiles $files) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(Patient $patient, User $registeredBy, array $data): LabSample
    {
        return DB::transaction(function () use ($patient, $registeredBy, $data): LabSample {
            $sample = LabSample::query()->create([
                ...$data,
                'code' => 'TMP-'.bin2hex(random_bytes(6)),
                'patient_id' => $patient->id,
                'registered_by' => $registeredBy->id,
                'status' => LabStatus::Registered,
            ]);
            $sample->forceFill(['code' => sprintf('LAB-%s-%06d', $sample->taken_on->format('Y'), $sample->id)])->save();

            return $sample;
        });
    }

    public function startAnalysis(LabSample $sample): void
    {
        $this->expect($sample, [LabStatus::Registered]);
        $sample->forceFill(['status' => LabStatus::InAnalysis])->save();
    }

    /**
     * Stores the result PDF. A result can be replaced until it is delivered.
     */
    public function attachResult(LabSample $sample, UploadedFile $file): void
    {
        $this->expect($sample, [LabStatus::InAnalysis, LabStatus::ResultReady]);
        $previous = $sample->result_path;
        $sample->forceFill([
            'result_path' => $this->files->storeResult($file),
            'status' => LabStatus::ResultReady,
            'result_ready_at' => CarbonImmutable::now(),
        ])->save();
        $this->files->delete($previous);

        $email = $sample->patient->email;
        if ($sample->notify_by_email && $email !== null && $email !== '') {
            Mail::to($email)->send(new LabResultReady($sample));
        }
    }

    public function deliver(LabSample $sample, DeliveryChannel $channel): void
    {
        $this->expect($sample, [LabStatus::ResultReady]);
        $sample->forceFill([
            'status' => LabStatus::Delivered,
            'delivered_at' => CarbonImmutable::now(),
            'delivery_channel' => $channel,
        ])->save();
    }

    /**
     * @param  list<LabStatus>  $allowed
     */
    private function expect(LabSample $sample, array $allowed): void
    {
        if (! in_array($sample->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'La muestra está '.mb_strtolower($sample->status->label()).' y no admite este paso.',
            ]);
        }
    }
}
