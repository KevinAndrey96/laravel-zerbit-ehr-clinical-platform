<x-layouts.app title="Historias clínicas">
    <x-page-header title="Tratamientos activos" subtitle="Historias clínicas abiertas. Para abrir una nueva, busca al paciente.">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn-primary">Buscar paciente</a>
        </x-slot:actions>
    </x-page-header>

    <nav class="mb-4 flex gap-2 text-sm" aria-label="Filtro">
        <a href="{{ route('histories.index') }}" @class(['rounded-full px-3 py-1', 'bg-brand-600 text-white' => $mine, 'bg-white text-slate-700 ring-1 ring-slate-200' => ! $mine])>Mis pacientes</a>
        <a href="{{ route('histories.index', ['vista' => 'todas']) }}" @class(['rounded-full px-3 py-1', 'bg-brand-600 text-white' => ! $mine, 'bg-white text-slate-700 ring-1 ring-slate-200' => $mine])>Todo el equipo</a>
    </nav>

    @if ($histories->isEmpty())
        <x-empty>No hay tratamientos activos.</x-empty>
    @else
        <div class="card overflow-hidden">
            <table class="table-base table-stack">
                <thead><tr><th>Paciente</th><th>Diagnóstico</th><th>Fisioterapeuta</th><th>Apertura</th><th class="num">Sesiones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($histories as $history)
                        <tr>
                            <td data-label="Paciente"><a href="{{ route('histories.show', $history) }}" class="link">{{ $history->patient->fullName() }}</a></td>
                            <td data-label="Diagnóstico">{{ $history->diagnosis_code }} · {{ $history->diagnosis }}</td>
                            <td data-label="Fisioterapeuta">{{ $history->therapist->fullName() }}</td>
                            <td data-label="Apertura" class="tabular-nums">{{ $fmt->date($history->opened_on) }}</td>
                            <td data-label="Sesiones" class="num">{{ $history->evolutions_count }} / {{ $history->authorized_sessions }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $histories->links() }}
    @endif
</x-layouts.app>
