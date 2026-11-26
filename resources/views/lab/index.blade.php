<x-layouts.app title="Laboratorio">
    <x-page-header title="Laboratorio clínico" subtitle="Seguimiento de muestras desde la toma hasta la entrega del resultado.">
        <x-slot:actions>
            <a href="{{ route('lab.create') }}" class="btn-primary">Registrar muestra</a>
        </x-slot:actions>
    </x-page-header>

    <nav class="mb-4 flex flex-wrap gap-2 text-sm" aria-label="Filtrar por estado">
        <a href="{{ route('lab.index') }}" @class(['rounded-full px-3 py-1', 'bg-brand-600 text-white' => $status === null, 'bg-white text-slate-700 ring-1 ring-slate-200' => $status !== null])>Pendientes</a>
        @foreach (\App\Enums\LabStatus::cases() as $option)
            <a href="{{ route('lab.index', ['estado' => $option->value]) }}" @class(['rounded-full px-3 py-1', 'bg-brand-600 text-white' => $status === $option, 'bg-white text-slate-700 ring-1 ring-slate-200' => $status !== $option])>{{ $option->label() }}</a>
        @endforeach
    </nav>

    @if ($samples->isEmpty())
        <x-empty>No hay muestras en este estado.</x-empty>
    @else
        <div class="card overflow-hidden">
            <table class="table-base table-stack">
                <thead><tr><th>Código</th><th>Paciente</th><th>Examen</th><th>Toma</th><th>Estado</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($samples as $sample)
                        <tr>
                            <td data-label="Código" class="tabular-nums"><a href="{{ route('lab.show', $sample) }}" class="link">{{ $sample->code }}</a></td>
                            <td data-label="Paciente">{{ $sample->patient->fullName() }}</td>
                            <td data-label="Examen">{{ $sample->exam }} <span class="text-xs text-slate-500">· {{ $sample->sample_type->label() }}</span></td>
                            <td data-label="Toma" class="tabular-nums">{{ $fmt->date($sample->taken_on) }}</td>
                            <td data-label="Estado"><x-badge :tone="$sample->status->tone()">{{ $sample->status->label() }}</x-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $samples->links() }}
    @endif
</x-layouts.app>
