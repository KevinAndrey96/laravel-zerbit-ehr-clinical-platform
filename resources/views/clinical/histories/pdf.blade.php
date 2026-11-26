<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historia clínica {{ $history->id }}</title>
    @include('pdf.style')
    <style>
        .field { margin-bottom: 6px; }
        .field b { display: block; font-size: 8px; color: #64748b; text-transform: uppercase; }
        .session { border: 1px solid #e2e8f0; padding: 6px 8px; margin-bottom: 6px; page-break-inside: avoid; }
        .signature { height: 42px; }
        .confidential { font-size: 8px; color: #b91c1c; }
    </style>
</head>
<body>
    <footer>{{ config('app.name') }} · Historia clínica N.º {{ $history->id }} · Impresa por {{ $printedBy->fullName() }} el {{ $fmt->dateTime($printedAt) }} · Documento confidencial (Resolución 1995 de 1999)</footer>

    <table class="header">
        <tr>
            <td>
                <h1>Historia clínica de fisioterapia</h1>
                <p class="muted">{{ config('app.name') }} · N.º {{ $history->id }} · Apertura {{ $fmt->date($history->opened_on) }}@if (! $history->isOpen()) · Alta {{ $fmt->date($history->closed_on) }}@endif</p>
            </td>
            <td style="text-align: right"><p class="confidential">CONFIDENCIAL</p></td>
        </tr>
    </table>

    <h2>Datos del paciente</h2>
    <table>
        <tr><th>Paciente</th><th>Documento</th><th>Edad</th><th>Sexo</th><th>Aseguradora</th></tr>
        <tr>
            <td>{{ $patient->fullName() }}</td>
            <td>{{ $patient->document() }}</td>
            <td>{{ $patient->age($history->opened_on)->label() }}</td>
            <td>{{ $patient->sex->label() }}</td>
            <td>{{ $patient->insurer?->name ?? 'Particular' }}</td>
        </tr>
    </table>

    <h2>Motivo, diagnóstico y plan</h2>
    <div class="field"><b>Motivo de consulta</b>{{ $history->reason }}</div>
    <div class="field"><b>Diagnóstico médico (CIE-10)</b>{{ $history->diagnosis_code }} · {{ $history->diagnosis }}</div>
    <div class="field"><b>Diagnóstico fisioterapéutico</b>{{ $history->physio_diagnosis }}</div>
    <div class="field"><b>Objetivo</b>{{ $history->objective }}</div>
    <div class="field"><b>Autorización</b>{{ $plan->authorized }} sesiones · N.º {{ $history->authorization_number ?? 'sin número' }} · copago {{ $fmt->cop($history->copayment_cop) }}</div>

    <h2>Antecedentes</h2>
    @foreach (\App\Models\ClinicalHistory::BACKGROUND as $field => $label)
        <div class="field"><b>{{ $label }}</b>{{ $history->text($field) ?? 'No refiere' }}</div>
    @endforeach

    <h2>Valoración inicial</h2>
    <div class="field"><b>Dolor (EVA)</b>{{ $history->pain_scale !== null ? $history->pain_scale.'/10' : 'Sin valorar' }} {{ $history->pain_notes }}</div>
    <div class="field"><b>Riesgo de caída</b>{{ $history->fall_risk?->label() ?? 'Sin valorar' }}</div>
    @foreach (\App\Models\ClinicalHistory::ASSESSMENT as $field => $label)
        @if ($history->text($field))
            <div class="field"><b>{{ $label }}</b>{{ $history->text($field) }}</div>
        @endif
    @endforeach

    <h2>Evolución ({{ $plan->held }} de {{ $plan->authorized }} sesiones)</h2>
    @forelse ($history->evolutions as $evolution)
        <div class="session">
            <table class="header">
                <tr>
                    <td><strong>Sesión {{ $evolution->session_number }}</strong> · {{ $fmt->date($evolution->attended_on) }} · {{ $evolution->schedule() }} · {{ $evolution->therapist->fullName() }}@if ($evolution->pain_scale !== null) · EVA {{ $evolution->pain_scale }}/10 @endif</td>
                    <td style="text-align: right; width: 120px">
                        @if ($signatures[$evolution->id])
                            <img src="{{ $signatures[$evolution->id] }}" class="signature" alt="">
                        @else
                            <span class="muted">Sin firma</span>
                        @endif
                    </td>
                </tr>
            </table>
            <p style="margin: 4px 0 0">{!! nl2br(e($evolution->note)) !!}</p>
        </div>
    @empty
        <p class="muted">Sin sesiones registradas.</p>
    @endforelse

    @if (! $history->isOpen())
        <h2>Resumen de alta</h2>
        <p>{!! nl2br(e($history->discharge_summary)) !!}</p>
    @endif

    <table class="header" style="margin-top: 24px">
        <tr>
            <td style="width: 50%">
                @if ($therapistSignature)
                    <img src="{{ $therapistSignature }}" class="signature" alt="">
                @endif
                <p style="border-top: 1px solid #94a3b8; padding-top: 3px; margin: 0">{{ $history->therapist->fullName() }}<br>
                <span class="muted">Fisioterapeuta · Registro {{ $history->therapist->professional_registry ?? 'sin registro' }}</span></p>
            </td>
            <td></td>
        </tr>
    </table>
</body>
</html>
