@php($status = $sample->status)
<x-layouts.app :title="'Muestra '.$sample->code">
    <x-page-header :title="'Muestra '.$sample->code" :subtitle="$sample->exam.' · '.$sample->sample_type->label()" :back="[route('lab.index'), 'Laboratorio']" />

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-6 lg:col-span-2" aria-labelledby="detalle">
            <div class="mb-4 flex items-center justify-between">
                <h2 id="detalle" class="font-semibold">Detalle</h2>
                <x-badge :tone="$status->tone()">{{ $status->label() }}</x-badge>
            </div>
            <dl class="dl-grid">
                <div><dt>Paciente</dt><dd><a href="{{ route('patients.show', $sample->patient) }}" class="link">{{ $sample->patient->fullName() }}</a></dd></div>
                <div><dt>Documento</dt><dd>{{ $sample->patient->document() }}</dd></div>
                <div><dt>Fecha de toma</dt><dd>{{ $fmt->date($sample->taken_on) }}</dd></div>
                <div><dt>Autorización</dt><dd>{{ $sample->authorization_number ?? 'Sin número' }}</dd></div>
                <div><dt>Registrada por</dt><dd>{{ $sample->registeredBy->fullName() }}</dd></div>
                <div><dt>Aviso por correo</dt><dd>{{ $sample->notify_by_email ? ($sample->patient->email ?? 'Activado, pero el paciente no tiene correo') : 'No' }}</dd></div>
                @if ($sample->result_ready_at)
                    <div><dt>Resultado listo</dt><dd>{{ $fmt->dateTime($sample->result_ready_at) }}</dd></div>
                @endif
                @if ($sample->delivered_at)
                    <div><dt>Entregado</dt><dd>{{ $fmt->dateTime($sample->delivered_at) }} · {{ $sample->delivery_channel?->label() }}</dd></div>
                @endif
            </dl>
        </section>

        <section class="card space-y-4 p-6" aria-labelledby="siguiente">
            <h2 id="siguiente" class="font-semibold">Siguiente paso</h2>
            @if ($status === \App\Enums\LabStatus::Registered)
                <form method="post" action="{{ route('lab.analyse', $sample) }}">
                    @csrf
                    <button class="btn-primary w-full">Pasar a análisis</button>
                </form>
            @endif
            @if (in_array($status, [\App\Enums\LabStatus::InAnalysis, \App\Enums\LabStatus::ResultReady], true))
                <form method="post" action="{{ route('lab.result', $sample) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label for="result" class="label">{{ $status->hasResult() ? 'Reemplazar resultado (PDF)' : 'Cargar resultado (PDF)' }}</label>
                    <input id="result" name="result" type="file" accept="application/pdf" required class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-800 hover:file:bg-brand-100">
                    <button class="btn-primary w-full">{{ $status->hasResult() ? 'Reemplazar' : 'Cargar resultado' }}</button>
                </form>
            @endif
            @if ($status->hasResult())
                <a href="{{ route('lab.download', $sample) }}" class="btn-secondary w-full">Ver resultado</a>
            @endif
            @if ($status === \App\Enums\LabStatus::ResultReady)
                <form method="post" action="{{ route('lab.deliver', $sample) }}" data-confirm="¿Confirmas que entregaste el resultado al paciente?">
                    @csrf
                    <button class="btn-secondary w-full">Marcar entregado en la clínica</button>
                </form>
                <p class="text-xs text-slate-500">Si el paciente lo descarga en el portal, queda entregado automáticamente.</p>
            @endif
            @if ($status === \App\Enums\LabStatus::Delivered)
                <p class="text-sm text-slate-600">Proceso completo.</p>
            @endif
        </section>
    </div>
</x-layouts.app>
