@php($editing = $member->exists)
<x-layouts.app :title="$editing ? 'Editar cuenta' : 'Nueva cuenta'">
    <x-page-header :title="$editing ? 'Editar a '.$member->fullName() : 'Nueva cuenta'" :back="[route('admin.staff.index'), 'Personal']" />

    <form method="post" action="{{ $editing ? route('admin.staff.update', $member) : route('admin.staff.store') }}" class="card grid max-w-3xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($editing)
            @method('put')
        @endif
        <x-field name="name" label="Nombres" :value="$member->name" required />
        <x-field name="last_name" label="Apellidos" :value="$member->last_name" required />
        <x-field name="email" label="Correo" type="email" :value="$member->email" autocomplete="off" required />
        <x-field name="document" label="Cédula" :value="$member->document" inputmode="numeric" required />
        <x-field name="role" label="Rol" type="select" required>
            @foreach (\App\Enums\Role::cases() as $role)
                <option value="{{ $role->value }}" @selected(old('role', $member->role?->value) === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </x-field>
        <x-field name="professional_registry" label="Registro profesional (RETHUS)" :value="$member->professional_registry" hint="Obligatorio para fisioterapeutas." />
        <x-field name="phone" label="Teléfono" type="tel" :value="$member->phone" />
        <label class="flex items-center gap-2 self-end pb-2 text-sm text-slate-700">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" @checked((bool) old('active', $member->active)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Cuenta activa
        </label>
        <x-field name="password" label="{{ $editing ? 'Nueva contraseña (opcional)' : 'Contraseña' }}" type="password" autocomplete="new-password" :required="! $editing" hint="Mínimo 10 caracteres con letras y números." />
        <x-field name="password_confirmation" label="Confirma la contraseña" type="password" autocomplete="new-password" :required="! $editing" />
        @error('active')
            <p class="text-sm text-red-700 sm:col-span-2">{{ $message }}</p>
        @enderror
        <div class="flex gap-2 sm:col-span-2">
            <button class="btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear cuenta' }}</button>
            <a href="{{ route('admin.staff.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
