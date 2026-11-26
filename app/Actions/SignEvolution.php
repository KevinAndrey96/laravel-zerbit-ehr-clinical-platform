<?php

namespace App\Actions;

use App\Domain\Signature\SignatureImage;
use App\Enums\AccessAction;
use App\Models\Evolution;
use App\Models\User;
use App\Support\ClinicalAudit;
use App\Support\PrivateFiles;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Attaches the patient's signature to a session note, once.
 */
final readonly class SignEvolution
{
    public function __construct(
        private PrivateFiles $files,
        private ClinicalAudit $audit,
    ) {}

    public function handle(Evolution $evolution, User $therapist, SignatureImage $signature): void
    {
        $path = $this->files->storeSignature($signature);
        try {
            DB::transaction(function () use ($evolution, $path): void {
                $locked = Evolution::query()->whereKey($evolution->id)->lockForUpdate()->firstOrFail();
                if ($locked->isSigned()) {
                    throw ValidationException::withMessages(['signature' => 'Esta sesión ya tiene la firma del paciente.'])
                        ->errorBag('signature');
                }
                $locked->forceFill(['patient_signature_path' => $path, 'signed_at' => CarbonImmutable::now()])->save();
            });
        } catch (ValidationException $exception) {
            $this->files->delete($path);

            throw $exception;
        }
        $this->audit->record($therapist, $evolution->clinicalHistory, AccessAction::Signed);
    }
}
