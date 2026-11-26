@php
    $background = collect(\App\Models\ClinicalHistory::BACKGROUND)->map(fn ($label, $field) => [$label, $history->text($field)])->filter(fn ($row) => $row[1] !== null);
    $assessment = collect(\App\Models\ClinicalHistory::ASSESSMENT)->map(fn ($label, $field) => [$label, $history->text($field)])->filter(fn ($row) => $row[1] !== null);
@endphp
<x-layouts.app :title="'Historia clínica · '.$patient->fullName()">
    <x-page-header :title="$patient->fullName()"
        :subtitle="$patient->document().' · '.$patient->age()->label().' · '.($patient->insurer?->name ?? 'Particular')"
        :back="[route('patients.show', $patient), 'Ficha del paciente']">
        <x-slot:actions>
            @can('update', $history)
                <a href="{{ route('histories.edit', $history) }}" class="btn-secondary">Editar valoración</a>
            @endcan
            <a href="{{ route('histories.pdf', $history) }}" class="btn-secondary">Descargar PDF</a>
        </x-slot:actions>
    </x-page-header>

    <section class="card mb-6 grid gap-4 p-5 sm:grid-cols-[1fr_auto] sm:items-center" aria-label="Estado del tratamiento">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <x-badge :tone="$history->isOpen() ? 'brand' : 'slate'">{{ $history->isOpen() ? 'Tratamiento activo' : 'Alta el '.$fmt->date($history->closed_on) }}</x-badge>
                <span class="text-sm text-slate-600">Historia N.º {{ $history->id }} · abierta el {{ $fmt->date($history->opened_on) }} por {{ $history->therapist->fullName() }}</span>
            </div>
            <x-progress :plan="$plan" class="mt-3 max-w-md" />
            <p class="mt-1 text-sm text-slate-700">{{ $plan->held }} de {{ $plan->authorized }} sesiones autorizadas · quedan {{ $plan->remaining() }}</p>
        </div>
        <dl class="text-sm sm:text-right">
            <dt class="text-xs text-slate-500 uppercase">Autorización</dt>
            <dd class="font-medium">{{ $history->authorization_number ?? 'Sin número' }}</dd>
            <dt class="mt-1 text-xs text-slate-500 uppercase">Copago</dt>
            <dd class="font-medium">{{ $fmt->cop($history->copayment_cop) }}</dd>
        </dl>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-6" aria-labelledby="diagnostico">
                <h2 id="diagnostico" class="mb-4 font-semibold">Motivo y diagnóstico</h2>
                <dl class="dl-grid lg:grid-cols-2">
                    <div class="sm:col-span-2"><dt>Motivo de consulta</dt><dd>{{ $history->reason }}</dd></div>
                    <div><dt>Diagnóstico médico</dt><dd><span class="font-medium">{{ $history->diagnosis_code }}</span> · {{ $history->diagnosis }}</dd></div>
                    <div><dt>Diagnóstico fisioterapéutico</dt><dd>{{ $history->physio_diagnosis }}</dd></div>
                    <div class="sm:col-span-2"><dt>Objetivo</dt><dd>{{ $history->objective }}</dd></div>
                </dl>
            </section>

            <section aria-labelledby="sesiones">
                <h2 id="sesiones" class="mb-3 font-semibold">Evolución por sesión</h2>
                @forelse ($history->evolutions as $evolution)
                    <article id="sesion-{{ $evolution->session_number }}" class="card mb-3 scroll-mt-4 p-5">
                        <header class="flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="font-semibold">Sesión {{ $evolution->session_number }}</h3>
                            <p class="text-xs text-slate-500">{{ ucfirst($fmt->longDate($evolution->attended_on)) }} · {{ $evolution->schedule() }} · {{ $evolution->therapist->fullName() }}@if ($evolution->pain_scale !== null) · EVA {{ $evolution->pain_scale }}/10 @endif</p>
                        </header>
                        <p class="mt-2 text-sm whitespace-pre-line text-slate-800">{{ $evolution->note }}</p>
                        <div class="mt-4 border-t border-slate-100 pt-3">
                            @if ($evolution->isSigned())
                                <p class="text-xs text-slate-500">Firma del paciente · {{ $fmt->dateTime($evolution->signed_at) }}</p>
                                @if ($signatures[$evolution->id])
                                    <img src="{{ $signatures[$evolution->id] }}" alt="Firma del paciente en la sesión {{ $evolution->session_number }}" class="mt-1 h-16 w-auto">
                                @endif
                            @else
                                @can('sign', $evolution)
                                    <p class="mb-2 text-sm font-medium text-amber-800">Pendiente la firma del paciente</p>
                                    <x-signature-pad :action="route('evolutions.sign', [$history, $evolution])" label="Guardar firma del paciente" :id="'firma-'.$evolution->id" />
                                @endcan
                            @endif
                        </div>
                    </article>
                @empty
                    <x-empty>Todavía no hay sesiones registradas.</x-empty>
                @endforelse
            </section>

            @can('evolve', $history)
                @if ($plan->isExhausted())
                    <div role="status" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Se completaron las {{ $plan->authorized }} sesiones autorizadas. Para continuar se necesita una nueva autorización y una nueva historia, o da de alta al paciente.
                    </div>
                @else
                    <section class="card p-6" aria-labelledby="nueva-sesion">
                        <h2 id="nueva-sesion" class="mb-4 font-semibold">Registrar la sesión {{ $plan->nextSessionNumber() }}</h2>
                        <form method="post" action="{{ route('evolutions.store', $history) }}" class="grid gap-4 sm:grid-cols-4">
                            @csrf
                            <x-field name="attended_on" label="Fecha" type="date" bag="evolution" :value="now()->toDateString()" :min="$history->opened_on->toDateString()" :max="now()->toDateString()" required />
                            <x-field name="starts_at" label="Inicio" type="time" bag="evolution" required />
                            <x-field name="ends_at" label="Fin" type="time" bag="evolution" required />
                            <x-field name="pain_scale" label="Dolor (EVA)" type="number" bag="evolution" min="0" max="10" />
                            <x-field name="note" label="Evolución" type="textarea" bag="evolution" rows="5" class="sm:col-span-4" required
                                hint="Intervención realizada, respuesta del paciente y plan para la próxima sesión. Una vez guardada no se puede modificar." />
                            <div class="sm:col-span-4"><button class="btn-primary">Guardar sesión</button></div>
                        </form>
                    </section>
                @endif
            @endcan
        </div>

        <aside class="space-y-6">
            <section class="card p-5" aria-labelledby="antecedentes">
                <h2 id="antecedentes" class="mb-3 font-semibold">Antecedentes</h2>
                @if ($background->isEmpty())
                    <p class="text-sm text-slate-600">Sin antecedentes relevantes.</p>
                @else
                    <dl class="space-y-2 text-sm">
                        @foreach ($background as [$label, $value])
                            <div><dt class="text-xs font-medium text-slate-500 uppercase">{{ $label }}</dt><dd class="whitespace-pre-line">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                @endif
            </section>

            <section class="card p-5" aria-labelledby="valoracion">
                <h2 id="valoracion" class="mb-3 font-semibold">Valoración inicial</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-xs font-medium text-slate-500 uppercase">Dolor (EVA)</dt><dd>{{ $history->pain_scale !== null ? $history->pain_scale.'/10' : 'Sin valorar' }}@if ($history->pain_notes) · {{ $history->pain_notes }}@endif</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500 uppercase">Riesgo de caída</dt><dd>{{ $history->fall_risk?->label() ?? 'Sin valorar' }}</dd></div>
                    @foreach ($assessment as [$label, $value])
                        <div><dt class="text-xs font-medium text-slate-500 uppercase">{{ $label }}</dt><dd class="whitespace-pre-line">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </section>

            @if (! $history->isOpen())
                <section class="card p-5" aria-labelledby="alta">
                    <h2 id="alta" class="mb-2 font-semibold">Resumen de alta</h2>
                    <p class="text-sm whitespace-pre-line">{{ $history->discharge_summary }}</p>
                </section>
            @endif

            @can('discharge', $history)
                <section class="card p-5" aria-labelledby="dar-alta">
                    <h2 id="dar-alta" class="mb-3 font-semibold">Dar de alta</h2>
                    <form method="post" action="{{ route('histories.discharge', $history) }}" class="space-y-3" data-confirm="¿Dar de alta al paciente? La historia quedará cerrada y no admitirá más sesiones.">
                        @csrf
                        <x-field name="closed_on" label="Fecha de alta" type="date" bag="discharge" :value="now()->toDateString()" :max="now()->toDateString()" required />
                        <x-field name="discharge_summary" label="Resumen de alta" type="textarea" bag="discharge" rows="4" required hint="Estado final, objetivos logrados y recomendaciones." />
                        <button class="btn-danger w-full">Dar de alta</button>
                    </form>
                </section>
            @endcan
        </aside>
    </div>
</x-layouts.app>
