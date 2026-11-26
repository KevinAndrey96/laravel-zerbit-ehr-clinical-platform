<?php

namespace App\Http\Controllers;

use App\Actions\LabWorkflow;
use App\Enums\DeliveryChannel;
use App\Enums\LabStatus;
use App\Http\Requests\LabSampleRequest;
use App\Models\LabSample;
use App\Models\Patient;
use App\Support\PrivateFiles;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LabSampleController extends Controller
{
    public function __construct(private readonly LabWorkflow $workflow) {}

    public function index(Request $request): View
    {
        $status = LabStatus::tryFrom($request->string('estado')->toString());
        $samples = LabSample::query()
            ->with('patient')
            ->when($status !== null, fn ($query) => $query->where('status', $status?->value))
            ->when($status === null, fn ($query) => $query->whereIn('status', LabStatus::pending()))
            ->latest('taken_on')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('lab.index', ['samples' => $samples, 'status' => $status]);
    }

    public function create(Request $request): View
    {
        $patient = $request->integer('paciente') > 0 ? Patient::query()->find($request->integer('paciente')) : null;

        return view('lab.create', [
            'patient' => $patient,
            'patients' => $patient === null ? Patient::query()->orderBy('first_surname')->orderBy('first_name')->get() : collect([$patient]),
            'today' => CarbonImmutable::today(),
        ]);
    }

    public function store(LabSampleRequest $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        $patient = Patient::query()->findOrFail($request->integer('patient_id'));
        $sample = $this->workflow->register($patient, $user, $request->sample());

        return redirect()->route('lab.show', $sample)->with('status', 'Muestra '.$sample->code.' registrada. Rotula el tubo con este código.');
    }

    public function show(LabSample $sample): View
    {
        return view('lab.show', ['sample' => $sample->load(['patient', 'registeredBy'])]);
    }

    public function analyse(LabSample $sample): RedirectResponse
    {
        $this->workflow->startAnalysis($sample);

        return back()->with('status', 'La muestra pasó a análisis.');
    }

    public function result(Request $request, LabSample $sample): RedirectResponse
    {
        $request->validate(['result' => ['required', 'file', 'mimes:pdf', 'max:10240']], [
            'result.mimes' => 'El resultado debe ser un archivo PDF.',
            'result.max' => 'El PDF no puede pesar más de 10 MB.',
        ]);
        $file = $request->file('result');
        abort_unless($file instanceof UploadedFile, Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->workflow->attachResult($sample, $file);

        $notified = $sample->notify_by_email && $sample->patient->email !== null;

        return back()->with('status', $notified
            ? 'Resultado cargado. Avisamos al paciente por correo para que lo descargue en el portal.'
            : 'Resultado cargado y listo para entregar.');
    }

    public function deliver(LabSample $sample): RedirectResponse
    {
        $this->workflow->deliver($sample, DeliveryChannel::InPerson);

        return back()->with('status', 'Resultado entregado en la clínica.');
    }

    public function download(LabSample $sample, PrivateFiles $files): StreamedResponse
    {
        abort_if($sample->result_path === null, Response::HTTP_NOT_FOUND);

        return $files->disk()->download($sample->result_path, $sample->downloadName());
    }
}
