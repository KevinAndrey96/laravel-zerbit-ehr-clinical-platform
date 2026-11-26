<?php

namespace App\Mail;

use App\Actions\SendPortalLink;
use App\Models\Patient;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class PortalAccessLink extends Mailable
{
    public function __construct(
        public readonly Patient $patient,
        public readonly string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu enlace de acceso al portal del paciente');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.portal-access-link', with: [
            'patient' => $this->patient,
            'url' => $this->url,
            'minutes' => SendPortalLink::MINUTES,
        ]);
    }
}
