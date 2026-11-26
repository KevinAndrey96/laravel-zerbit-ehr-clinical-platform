<?php

namespace App\Http\Controllers;

use App\Enums\LabStatus;
use App\Models\Appointment;
use App\Models\ClinicalHistory;
use App\Models\Evolution;
use App\Models\LabSample;
use App\Models\Patient;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $this->currentUser($request);
        $today = CarbonImmutable::today();

        return $user->isAdmin() ? $this->front($today) : $this->therapist($user, $today);
    }

    private function front(CarbonImmutable $today): View
    {
        return view('dashboard.admin', [
            'today' => $today,
            'agenda' => Appointment::query()->with(['patient', 'therapist'])->on($today)->orderBy('starts_at')->get(),
            'patients' => Patient::query()->count(),
            'pendingSamples' => LabSample::query()->whereIn('status', [LabStatus::Registered, LabStatus::InAnalysis])->count(),
            'readySamples' => LabSample::query()->with('patient')->where('status', LabStatus::ResultReady)->oldest('result_ready_at')->limit(8)->get(),
            'readyCount' => LabSample::query()->where('status', LabStatus::ResultReady)->count(),
        ]);
    }

    private function therapist(User $user, CarbonImmutable $today): View
    {
        return view('dashboard.therapist', [
            'today' => $today,
            'agenda' => $user->appointments()->with('patient')->on($today)->orderBy('starts_at')->get(),
            'histories' => ClinicalHistory::query()->with('patient')->withCount('evolutions')->open()
                ->where('therapist_id', $user->id)->latest('opened_on')->limit(12)->get(),
            'unsigned' => Evolution::query()->with('clinicalHistory.patient')->where('therapist_id', $user->id)
                ->whereNull('signed_at')->latest('attended_on')->limit(8)->get(),
            'hasSignature' => $user->signature_path !== null,
        ]);
    }
}
