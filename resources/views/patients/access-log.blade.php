<x-layouts.app title="Registro de accesos">
    <x-page-header title="Registro de accesos a la historia clínica" :subtitle="$patient->fullName().' · '.$patient->document()" :back="[route('patients.show', $patient), 'Ficha del paciente']" />

    <p class="mb-4 max-w-3xl text-sm text-slate-600">Cada consulta, impresión o modificación de la historia clínica queda registrada con el usuario, la fecha y la dirección IP, como exige la custodia de la historia clínica (Resolución 1995 de 1999).</p>

    @if ($logs->isEmpty())
        <x-empty>Nadie ha consultado aún la historia clínica de este paciente.</x-empty>
    @else
        <div class="card overflow-hidden">
            <table class="table-base table-stack">
                <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Historia</th><th>IP</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        <tr>
                            <td data-label="Fecha" class="tabular-nums">{{ $fmt->dateTime($log->created_at) }}</td>
                            <td data-label="Usuario">{{ $log->user->fullName() }}</td>
                            <td data-label="Acción">{{ $log->action->label() }}</td>
                            <td data-label="Historia" class="tabular-nums">{{ $log->clinical_history_id ? 'N.º '.$log->clinical_history_id : '' }}</td>
                            <td data-label="IP" class="tabular-nums">{{ $log->ip_address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    @endif
</x-layouts.app>
