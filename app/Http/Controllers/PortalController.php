<?php

namespace App\Http\Controllers;

use App\Actions\LabWorkflow;
use App\Actions\SendPortalLink;
use App\Enums\DeliveryChannel;
use App\Enums\DocumentType;
use App\Enums\LabStatus;
use App\Http\Middleware\EnsurePortalPatient;
use App\Models\LabSample;
use App\Models\Patient;
use App\Support\PrivateFiles;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Patient self-service: lab results and upcoming appointments, reached
 * through a one-time signed link sent to the email on file.
 */
final class PortalController extends Controller
{
    public function login(): View
    {
        return view('portal.login');
    }

    public function sendLink(Request $request, SendPortalLink $send): RedirectResponse
    {
        $data = $request->validate([
            'document_type' => ['required', Rule::enum(DocumentType::class)],
            'document_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:120'],
        ]);
        $number = strtoupper(preg_replace('/[\s.\-]/', '', $data['document_number']) ?? '');
        $send->handle(DocumentType::from($data['document_type']), $number, $data['email']);

        return redirect()->route('portal.login')->with('status', 'Si los datos coinciden con nuestros registros, en unos minutos te llegará un enlace de acceso al correo. Revisa también la carpeta de spam.');
    }

    public function enter(Request $request, Patient $patient): RedirectResponse
    {
        $request->session()->regenerate();
        $request->session()->put(EnsurePortalPatient::SESSION_KEY, $patient->id);
        $request->session()->put(EnsurePortalPatient::SEEN_KEY, CarbonImmutable::now()->getTimestamp());

        return redirect()->route('portal.home');
    }

    public function home(Request $request): View
    {
        $patient = $this->patient($request);

        return view('portal.home', [
            'patient' => $patient,
            'results' => $patient->labSamples()->whereIn('status', [LabStatus::ResultReady, LabStatus::Delivered])->latest('result_ready_at')->get(),
            'pending' => $patient->labSamples()->whereIn('status', [LabStatus::Registered, LabStatus::InAnalysis])->latest('taken_on')->get(),
            'appointments' => $patient->appointments()->with('therapist')->holdingSlot()
                ->where('starts_at', '>=', CarbonImmutable::now())->orderBy('starts_at')->get(),
        ]);
    }

    public function download(Request $request, LabSample $sample, LabWorkflow $workflow, PrivateFiles $files): StreamedResponse
    {
        $path = $sample->result_path;
        abort_unless($sample->patient_id === $this->patient($request)->id, Response::HTTP_NOT_FOUND);
        abort_unless($sample->status->hasResult() && $path !== null, Response::HTTP_NOT_FOUND);
        if ($sample->status === LabStatus::ResultReady) {
            $workflow->deliver($sample, DeliveryChannel::Portal);
        }

        return $files->disk()->download($path, $sample->downloadName());
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([EnsurePortalPatient::SESSION_KEY, EnsurePortalPatient::SEEN_KEY]);
        $request->session()->regenerate();

        return redirect()->route('portal.login')->with('status', 'Cerraste la sesión del portal.');
    }

    private function patient(Request $request): Patient
    {
        $patient = $request->attributes->get('patient');
        abort_unless($patient instanceof Patient, Response::HTTP_UNAUTHORIZED);

        return $patient;
    }
}
