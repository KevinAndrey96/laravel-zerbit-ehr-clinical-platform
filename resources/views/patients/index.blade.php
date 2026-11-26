<x-layouts.app title="Pacientes">
    <x-page-header title="Pacientes" subtitle="Busca por nombre, apellido o número de documento.">
        <x-slot:actions>
            <a href="{{ route('patients.create') }}" class="btn-primary">Nuevo paciente</a>
        </x-slot:actions>
    </x-page-header>

    <form method="get" action="{{ route('patients.index') }}" class="mb-4 flex max-w-xl gap-2" role="search">
        <label for="q" class="sr-only">Buscar paciente</label>
        <input id="q" name="q" type="search" value="{{ $term }}" class="input" placeholder="Ej.: 1012345678 o Ramírez" autofocus>
        <button class="btn-secondary">Buscar</button>
    </form>

    @if ($patients->isEmpty())
        <x-empty>
            @if ($term !== '')
                No encontramos pacientes para «{{ $term }}». <a href="{{ route('patients.create') }}" class="link">Regístralo</a>.
            @else
                Aún no hay pacientes registrados.
            @endif
        </x-empty>
    @else
        <div class="card overflow-hidden">
            <table class="table-base table-stack">
                <thead><tr><th>Paciente</th><th>Documento</th><th>Edad</th><th>Aseguradora</th><th>Teléfono</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($patients as $patient)
                        <tr>
                            <td data-label="Paciente"><a href="{{ route('patients.show', $patient) }}" class="link">{{ $patient->fullName() }}</a></td>
                            <td data-label="Documento" class="tabular-nums">{{ $patient->document() }}</td>
                            <td data-label="Edad">{{ $patient->age()->label() }}</td>
                            <td data-label="Aseguradora">{{ $patient->insurer?->name ?? 'Particular' }}</td>
                            <td data-label="Teléfono" class="tabular-nums">{{ $patient->phone }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $patients->links() }}
    @endif
</x-layouts.app>
