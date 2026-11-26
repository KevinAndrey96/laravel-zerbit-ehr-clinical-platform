<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\AccessLog;
use App\Models\Insurer;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim($request->string('q')->toString());
        $patients = Patient::query()
            ->with('insurer')
            ->when($term !== '', fn ($query) => $query->search($term))
            ->orderBy('first_surname')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('patients.index', ['patients' => $patients, 'term' => $term]);
    }

    public function create(): View
    {
        return view('patients.form', ['patient' => new Patient, 'insurers' => $this->insurers()]);
    }

    public function store(PatientRequest $request): RedirectResponse
    {
        $patient = Patient::query()->create($request->validated());

        return redirect()->route('patients.show', $patient)->with('status', 'Paciente registrado.');
    }

    public function show(Patient $patient): View
    {
        $patient->load('insurer');

        return view('patients.show', [
            'patient' => $patient,
            'histories' => $patient->clinicalHistories()->with('therapist')->withCount('evolutions')->latest('opened_on')->get(),
            'appointments' => $patient->appointments()->with('therapist')->where('starts_at', '>=', CarbonImmutable::today())->orderBy('starts_at')->limit(10)->get(),
            'samples' => $patient->labSamples()->latest('taken_on')->limit(10)->get(),
        ]);
    }

    public function edit(Patient $patient): View
    {
        return view('patients.form', ['patient' => $patient, 'insurers' => $this->insurers($patient->insurer_id)]);
    }

    public function update(PatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update($request->validated());

        return redirect()->route('patients.show', $patient)->with('status', 'Datos del paciente actualizados.');
    }

    public function accessLog(Patient $patient): View
    {
        return view('patients.access-log', [
            'patient' => $patient,
            'logs' => AccessLog::query()->with('user')->where('patient_id', $patient->id)->latest('created_at')->latest('id')->paginate(30),
        ]);
    }

    /**
     * Active insurers plus the patient's current one, even if it was deactivated.
     *
     * @return Collection<int, Insurer>
     */
    private function insurers(?int $current = null): Collection
    {
        return Insurer::query()
            ->where(fn ($query) => $query->where('active', true)->when($current !== null, fn ($q) => $q->orWhere('id', $current)))
            ->orderBy('name')
            ->get();
    }
}
