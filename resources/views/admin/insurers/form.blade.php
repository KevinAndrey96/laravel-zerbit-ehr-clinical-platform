@php($editing = $insurer->exists)
<x-layouts.app :title="$editing ? 'Editar aseguradora' : 'Nueva aseguradora'">
    <x-page-header :title="$editing ? 'Editar '.$insurer->name : 'Nueva aseguradora'" :back="[route('admin.insurers.index'), 'Aseguradoras']" />

    <form method="post" action="{{ $editing ? route('admin.insurers.update', $insurer) : route('admin.insurers.store') }}" class="card grid max-w-2xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($editing)
            @method('put')
        @endif
        <x-field name="name" label="Nombre" :value="$insurer->name" required class="sm:col-span-2" />
        <x-field name="nit" label="NIT" :value="$editing ? $insurer->formattedNit() : null" placeholder="900.123.456-1" hint="Con o sin dígito de verificación." required />
        <label class="flex items-center gap-2 self-end pb-2 text-sm text-slate-700">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" @checked((bool) old('active', $insurer->active)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Activa para nuevos pacientes
        </label>
        <div class="flex gap-2 sm:col-span-2">
            <button class="btn-primary">{{ $editing ? 'Guardar cambios' : 'Registrar aseguradora' }}</button>
            <a href="{{ route('admin.insurers.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
