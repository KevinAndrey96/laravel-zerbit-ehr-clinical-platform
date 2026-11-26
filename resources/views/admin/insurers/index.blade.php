<x-layouts.app title="Aseguradoras">
    <x-page-header title="Aseguradoras" subtitle="EPS, planes complementarios y ARL con las que trabaja la clínica.">
        <x-slot:actions>
            <a href="{{ route('admin.insurers.create') }}" class="btn-primary">Nueva aseguradora</a>
        </x-slot:actions>
    </x-page-header>

    @if ($insurers->isEmpty())
        <x-empty>No hay aseguradoras registradas.</x-empty>
    @else
        <div class="card overflow-hidden">
            <table class="table-base table-stack">
                <thead><tr><th>Nombre</th><th>NIT</th><th class="num">Pacientes</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($insurers as $insurer)
                        <tr>
                            <td data-label="Nombre">{{ $insurer->name }}</td>
                            <td data-label="NIT" class="tabular-nums">{{ $insurer->formattedNit() }}</td>
                            <td data-label="Pacientes" class="num">{{ $insurer->patients_count }}</td>
                            <td data-label="Estado"><x-badge :tone="$insurer->active ? 'emerald' : 'slate'">{{ $insurer->active ? 'Activa' : 'Inactiva' }}</x-badge></td>
                            <td data-label="Acciones"><a href="{{ route('admin.insurers.edit', $insurer) }}" class="link">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
