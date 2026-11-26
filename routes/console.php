<?php

use App\Actions\SendAppointmentReminder;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('appointments:send-reminders', function (SendAppointmentReminder $remind): void {
    $sent = 0;
    Appointment::query()
        ->with(['patient', 'therapist'])
        ->holdingSlot()
        ->on(CarbonImmutable::tomorrow())
        ->whereNull('reminder_sent_at')
        ->whereHas('patient', fn ($query) => $query->whereNotNull('email'))
        ->each(function (Appointment $appointment) use ($remind, &$sent): void {
            $remind->handle($appointment);
            $sent++;
        });
    $this->info("Recordatorios enviados: {$sent}");
})->purpose('Email tomorrow\'s appointment reminders to patients');

Schedule::command('appointments:send-reminders')->dailyAt('17:00')->withoutOverlapping();
