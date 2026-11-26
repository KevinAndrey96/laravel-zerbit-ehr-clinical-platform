<x-layouts.app title="Personal">
    <x-page-header title="Personal" subtitle="Cuentas de fisioterapeutas y personal administrativo.">
        <x-slot:actions>
            <a href="{{ route('admin.staff.create') }}" class="btn-primary">Nueva cuenta</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card overflow-hidden">
        <table class="table-base table-stack">
            <thead><tr><th>Nombre</th><th>Rol</th><th>Correo</th><th>Registro profesional</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($staff as $member)
                    <tr>
                        <td data-label="Nombre">{{ $member->fullName() }}</td>
                        <td data-label="Rol">{{ $member->role->label() }}</td>
                        <td data-label="Correo">{{ $member->email }}</td>
                        <td data-label="Registro">{{ $member->professional_registry ?? 'No aplica' }}</td>
                        <td data-label="Estado"><x-badge :tone="$member->active ? 'emerald' : 'slate'">{{ $member->active ? 'Activa' : 'Inactiva' }}</x-badge></td>
                        <td data-label="Acciones"><a href="{{ route('admin.staff.edit', $member) }}" class="link">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
