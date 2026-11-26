@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $open = $histories->first(fn ($history) => $history->isOpen());
@endphp
<x-layouts.app :title="$patient->fullName()">
    <x-page-header :title="$patient->fullName()" :subtitle="$patient->document().' · '.$patient->age()->label()" :back="[route('patients.index'), 'Pacientes']">
        <x-slot:actions>
            <a href="{{ route('patients.edit', $patient) }}" class="btn-secondary">Editar datos</a>
            @if ($user->isAdmin())
                <a href="{{ route('patients.access-log', $patient) }}" class="btn-secondary">Registro de accesos</a>
                <a href="{{ route('lab.create', ['paciente' => $patient->id]) }}" class="btn-secondary">Registrar muestra</a>
                <a href="{{ route('appointments.create', ['paciente' => $patient->id]) }}" class="btn-primary">Agendar cita</a>
            @elseif ($open === null)
                <a href="{{ route('histories.create', $patient) }}" class="btn-primary">Abrir historia clínica</a>
            @else
                <a href="{{ route('histories.show', $open) }}" class="btn-primary">Ir al tratamiento activo</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <section class="card p-6" aria-labelledby="datos">
        <h2 id="datos" class="sr-only">Datos del paciente</h2>
        <dl class="dl-grid">
            <div><dt>Aseguradora</dt><dd>{{ $patient->insurer?->name ?? 'Particular' }}</dd></div>
            <div><dt>Fecha de nacimiento</dt><dd>{{ $fmt->date($patient->birthdate) }}</dd></div>
            <div><dt>Sexo</dt><dd>{{ $patient->sex->label() }}</dd></div>
            <div><dt>Teléfono</dt><dd>{{ $patient->phone }}</dd></div>
            <div><dt>Correo</dt><dd>{{ $patient->email ?? 'Sin correo' }}</dd></div>
            <div><dt>Dirección</dt><dd>{{ collect([$patient->address, $patient->city])->filter()->implode(', ') ?: 'Sin dato' }}</dd></div>
            <div><dt>Estado civil</dt><dd>{{ $patient->civil_status?->label() ?? 'Sin dato' }}</dd></div>
            <div><dt>Ocupación</dt><dd>{{ $patient->occupation ?? 'Sin dato' }}</dd></div>
        </dl>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card" aria-labelledby="historias">
            <h2 id="historias" class="border-b border-slate-200 px-4 py-3 font-semibold">Tratamientos de fisioterapia</h2>
            @forelse ($histories as $history)
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 text-sm last:border-0">
                    <div>
                        <p class="font-medium">{{ $history->diagnosis_code }} · {{ $history->diagnosis }}</p>
                        <p class="text-xs text-slate-500">Desde {{ $fmt->date($history->opened_on) }} · {{ $history->therapist->fullName() }} · {{ $history->evolutions_count }} de {{ $history->authorized_sessions }} sesiones</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <x-badge :tone="$history->isOpen() ? 'brand' : 'slate'">{{ $history->isOpen() ? 'Activo' : 'Alta' }}</x-badge>
                        @can('view', $history)
                            <a href="{{ route('histories.show', $history) }}" class="link">Abrir</a>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="px-4 py-6 text-sm text-slate-600">Sin tratamientos registrados.</p>
            @endforelse
            @if ($user->isAdmin() && $histories->isNotEmpty())
                <p class="border-t border-slate-100 px-4 py-2 text-xs text-slate-500">El contenido clínico solo es visible para el equipo de fisioterapia.</p>
            @endif
        </section>

        <div class="space-y-6">
            <section class="card" aria-labelledby="proximas">
                <h2 id="proximas" class="border-b border-slate-200 px-4 py-3 font-semibold">Próximas citas</h2>
                @forelse ($appointments as $appointment)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 text-sm last:border-0">
                        <span>{{ ucfirst($fmt->longDate($appointment->starts_at)) }}, {{ $fmt->time($appointment->starts_at) }} · {{ $appointment->therapist->fullName() }}</span>
                        <x-badge :tone="$appointment->status->tone()">{{ $appointment->status->label() }}</x-badge>
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-slate-600">Sin citas próximas.</p>
                @endforelse
            </section>

            @if ($user->isAdmin())
                <section class="card" aria-labelledby="laboratorio">
                    <h2 id="laboratorio" class="border-b border-slate-200 px-4 py-3 font-semibold">Laboratorio</h2>
                    @forelse ($samples as $sample)
                        <a href="{{ route('lab.show', $sample) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 text-sm last:border-0 hover:bg-slate-50">
                            <span>{{ $sample->code }} · {{ $sample->exam }} · {{ $fmt->date($sample->taken_on) }}</span>
                            <x-badge :tone="$sample->status->tone()">{{ $sample->status->label() }}</x-badge>
                        </a>
                    @empty
                        <p class="px-4 py-6 text-sm text-slate-600">Sin muestras registradas.</p>
                    @endforelse
                </section>
            @endif
        </div>
    </div>
</x-layouts.app>
