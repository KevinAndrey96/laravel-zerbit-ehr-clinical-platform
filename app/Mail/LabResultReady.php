<?php

namespace App\Mail;

use App\Models\LabSample;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class LabResultReady extends Mailable
{
    public function __construct(public readonly LabSample $sample) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu resultado de laboratorio está listo');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lab-result-ready', with: ['sample' => $this->sample]);
    }
}
