<?php

namespace App\Actions;

use App\Enums\DocumentType;
use App\Mail\PortalAccessLink;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Emails a short-lived signed link to the patient's portal. The caller
 * always answers the same way so the form cannot be used to find out
 * whether someone is a patient.
 */
final class SendPortalLink
{
    public const int MINUTES = 20;

    public function handle(DocumentType $type, string $number, string $email): void
    {
        $patient = Patient::query()
            ->where('document_type', $type->value)
            ->where('document_number', $number)
            ->first();
        if ($patient === null || $patient->email === null || mb_strtolower($patient->email) !== mb_strtolower($email)) {
            return;
        }

        $url = URL::temporarySignedRoute('portal.enter', CarbonImmutable::now()->addMinutes(self::MINUTES), ['patient' => $patient->id]);
        Mail::to($patient->email)->send(new PortalAccessLink($patient, $url));
    }
}
