<?php

namespace App\Http\Controllers;

use App\Actions\ChangeAppointmentStatus;
use App\Actions\ScheduleAppointment;
use App\Actions\SendAppointmentReminder;
use App\Enums\AppointmentStatus;
use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

final class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->currentUser($request);
        $day = $this->day($request->string('dia')->toString());
        $therapistId = $user->isTherapist() ? $user->id : $request->integer('fisioterapeuta');

        $appointments = Appointment::query()
            ->with(['patient', 'therapist'])
            ->on($day)
            ->when($therapistId > 0, fn ($query) => $query->where('therapist_id', $therapistId))
            ->orderBy('starts_at')
            ->get();

        return view('appointments.index', [
            'day' => $day,
            'appointments' => $appointments,
            'therapists' => $user->isAdmin() ? User::query()->therapists()->active()->orderBy('name')->get() : collect(),
            'therapistId' => $therapistId,
        ]);
    }

    public function create(Request $request): View
    {
        $patient = $request->integer('paciente') > 0 ? Patient::query()->find($request->integer('paciente')) : null;

        return view('appointments.create', [
            'patient' => $patient,
            'patients' => $patient === null ? Patient::query()->orderBy('first_surname')->orderBy('first_name')->get() : collect([$patient]),
            'therapists' => User::query()->therapists()->active()->orderBy('name')->get(),
            'durations' => AppointmentRequest::DURATIONS,
            'start' => CarbonImmutable::tomorrow()->setTime(8, 0),
        ]);
    }

    public function store(AppointmentRequest $request, ScheduleAppointment $schedule): RedirectResponse
    {
        $appointment = $schedule->handle($request->appointment());

        return redirect()->route('appointments.index', ['dia' => $appointment->starts_at->toDateString()])
            ->with('status', 'Cita programada para '.$appointment->patient->fullName().'.');
    }

    public function status(Request $request, Appointment $appointment, ChangeAppointmentStatus $change): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);
        $status = AppointmentStatus::from($data['status']);
        $change->handle($appointment, $status, $data['cancellation_reason'] ?? null);

        return back()->with('status', 'Cita marcada como '.mb_strtolower($status->label()).'.');
    }

    public function remind(Appointment $appointment, SendAppointmentReminder $remind): RedirectResponse
    {
        $remind->handle($appointment);

        return back()->with('status', 'Recordatorio enviado a '.$appointment->patient->email.'.');
    }

    private function day(string $value): CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            try {
                return CarbonImmutable::createFromFormat('!Y-m-d', $value) ?: CarbonImmutable::today();
            } catch (Throwable) {
                return CarbonImmutable::today();
            }
        }

        return CarbonImmutable::today();
    }
}
