<?php

namespace App\Http\Controllers\Clinical;

use App\Actions\DischargePatient;
use App\Actions\OpenClinicalHistory;
use App\Enums\AccessAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinical\ClinicalHistoryRequest;
use App\Http\Requests\Clinical\DischargeRequest;
use App\Models\ClinicalHistory;
use App\Models\Patient;
use App\Support\ClinicalAudit;
use App\Support\PrivateFiles;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ClinicalHistoryController extends Controller
{
    public function __construct(private readonly ClinicalAudit $audit) {}

    public function index(Request $request): View
    {
        $mine = $request->query('vista') !== 'todas';
        $histories = ClinicalHistory::query()
            ->with(['patient', 'therapist'])
            ->withCount('evolutions')
            ->open()
            ->when($mine, fn ($query) => $query->where('therapist_id', $this->currentUser($request)->id))
            ->latest('opened_on')
            ->paginate(20)
            ->withQueryString();

        return view('clinical.histories.index', ['histories' => $histories, 'mine' => $mine]);
    }

    public function create(Patient $patient): View
    {
        return view('clinical.histories.form', [
            'patient' => $patient,
            'history' => new ClinicalHistory(['opened_on' => CarbonImmutable::today(), 'authorized_sessions' => 10]),
        ]);
    }

    public function store(ClinicalHistoryRequest $request, Patient $patient, OpenClinicalHistory $open): RedirectResponse
    {
        $history = $open->handle($patient, $this->currentUser($request), $request->clinicalData());

        return redirect()->route('histories.show', $history)->with('status', 'Historia clínica abierta. Ya puedes registrar la primera sesión.');
    }

    public function show(Request $request, ClinicalHistory $history, PrivateFiles $files): View
    {
        $history->load(['patient.insurer', 'therapist', 'evolutions.therapist'])->loadCount('evolutions');
        $this->audit->record($this->currentUser($request), $history, AccessAction::Viewed);

        return view('clinical.histories.show', [
            'history' => $history,
            'patient' => $history->patient,
            'plan' => $history->plan(),
            'signatures' => $history->evolutions->mapWithKeys(fn ($evolution) => [$evolution->id => $files->dataUrl($evolution->patient_signature_path)]),
        ]);
    }

    public function edit(ClinicalHistory $history): View
    {
        return view('clinical.histories.form', ['patient' => $history->patient, 'history' => $history]);
    }

    public function update(ClinicalHistoryRequest $request, ClinicalHistory $history): RedirectResponse
    {
        $history->update($request->clinicalData());

        return redirect()->route('histories.show', $history)->with('status', 'Valoración inicial actualizada.');
    }

    public function discharge(DischargeRequest $request, ClinicalHistory $history, DischargePatient $discharge): RedirectResponse
    {
        $discharge->handle(
            $history,
            $this->currentUser($request),
            $request->string('closed_on')->toString(),
            $request->string('discharge_summary')->toString(),
        );

        return redirect()->route('histories.show', $history)->with('status', 'Paciente dado de alta. La historia quedó cerrada.');
    }

    public function pdf(Request $request, ClinicalHistory $history, PrivateFiles $files): Response
    {
        $history->load(['patient.insurer', 'therapist', 'evolutions.therapist'])->loadCount('evolutions');
        $this->audit->record($this->currentUser($request), $history, AccessAction::Downloaded);

        return Pdf::loadView('clinical.histories.pdf', [
            'history' => $history,
            'patient' => $history->patient,
            'plan' => $history->plan(),
            'signatures' => $history->evolutions->mapWithKeys(fn ($evolution) => [$evolution->id => $files->dataUrl($evolution->patient_signature_path)]),
            'therapistSignature' => $files->dataUrl($history->therapist->signature_path),
            'printedBy' => $this->currentUser($request),
            'printedAt' => CarbonImmutable::now(),
        ])->download('historia-clinica-'.$history->id.'.pdf');
    }
}
