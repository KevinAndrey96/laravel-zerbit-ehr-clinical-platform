<x-layouts.app title="Resumen">
    <x-page-header title="Resumen del día" :subtitle="ucfirst($fmt->longDate($today))">
        <x-slot:actions>
            <a href="{{ route('patients.create') }}" class="btn-secondary">Nuevo paciente</a>
            <a href="{{ route('appointments.create') }}" class="btn-primary">Agendar cita</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Citas de hoy" :value="$agenda->count()" :hint="$agenda->where('status', \App\Enums\AppointmentStatus::Attended)->count().' atendidas'" />
        <x-stat label="Pacientes registrados" :value="$fmt->number($patients)" />
        <x-stat label="Muestras en proceso" :value="$pendingSamples" hint="Registradas o en análisis" />
        <x-stat label="Resultados por entregar" :value="$readyCount" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="card lg:col-span-2" aria-labelledby="agenda-hoy">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h2 id="agenda-hoy" class="font-semibold">Agenda de hoy</h2>
                <a href="{{ route('appointments.index') }}" class="link text-sm">Ver agenda</a>
            </div>
            @if ($agenda->isEmpty())
                <x-empty class="m-4">No hay citas para hoy.</x-empty>
            @else
                <table class="table-base table-stack">
                    <thead><tr><th>Hora</th><th>Paciente</th><th>Fisioterapeuta</th><th>Estado</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($agenda as $appointment)
                            <tr>
                                <td data-label="Hora" class="tabular-nums">{{ $fmt->time($appointment->starts_at) }}</td>
                                <td data-label="Paciente"><a href="{{ route('patients.show', $appointment->patient) }}" class="link">{{ $appointment->patient->fullName() }}</a></td>
                                <td data-label="Fisioterapeuta">{{ $appointment->therapist->fullName() }}</td>
                                <td data-label="Estado"><x-badge :tone="$appointment->status->tone()">{{ $appointment->status->label() }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="card" aria-labelledby="por-entregar">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h2 id="por-entregar" class="font-semibold">Resultados por entregar</h2>
                <a href="{{ route('lab.index', ['estado' => 'resultado_listo']) }}" class="link text-sm">Ver todos</a>
            </div>
            @forelse ($readySamples as $sample)
                <a href="{{ route('lab.show', $sample) }}" class="block border-b border-slate-100 px-4 py-3 text-sm last:border-0 hover:bg-slate-50">
                    <span class="font-medium text-slate-900">{{ $sample->patient->fullName() }}</span>
                    <span class="block text-xs text-slate-500">{{ $sample->code }} · {{ $sample->exam }} · listo desde {{ $fmt->date($sample->result_ready_at) }}</span>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-slate-600">Todo está entregado.</p>
            @endforelse
        </section>
    </div>
</x-layouts.app>
