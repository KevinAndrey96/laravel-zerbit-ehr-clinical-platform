@php($editing = $patient->exists)
<x-layouts.app :title="$editing ? 'Editar paciente' : 'Nuevo paciente'">
    <x-page-header :title="$editing ? 'Editar '.$patient->fullName() : 'Nuevo paciente'"
        :back="$editing ? [route('patients.show', $patient), 'Ficha del paciente'] : [route('patients.index'), 'Pacientes']" />

    <form method="post" action="{{ $editing ? route('patients.update', $patient) : route('patients.store') }}" class="card space-y-6 p-6">
        @csrf
        @if ($editing)
            @method('put')
        @endif

        <fieldset class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <legend class="mb-2 text-sm font-semibold text-slate-900">Identificación</legend>
            <x-field name="document_type" label="Tipo de documento" type="select" required>
                @foreach (\App\Enums\DocumentType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('document_type', $patient->document_type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-field>
            <x-field name="document_number" label="Número de documento" :value="$patient->document_number" inputmode="text" autocomplete="off" required />
            <x-field name="birthdate" label="Fecha de nacimiento" type="date" :value="$patient->birthdate" :max="now()->toDateString()" required />
            <x-field name="sex" label="Sexo" type="select" required>
                <option value="">Selecciona</option>
                @foreach (\App\Enums\Sex::cases() as $sex)
                    <option value="{{ $sex->value }}" @selected(old('sex', $patient->sex?->value) === $sex->value)>{{ $sex->label() }}</option>
                @endforeach
            </x-field>
            <x-field name="first_name" label="Primer nombre" :value="$patient->first_name" autocomplete="off" required />
            <x-field name="second_name" label="Segundo nombre" :value="$patient->second_name" autocomplete="off" />
            <x-field name="first_surname" label="Primer apellido" :value="$patient->first_surname" autocomplete="off" required />
            <x-field name="second_surname" label="Segundo apellido" :value="$patient->second_surname" autocomplete="off" />
        </fieldset>

        <fieldset class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <legend class="mb-2 text-sm font-semibold text-slate-900">Contacto y afiliación</legend>
            <x-field name="phone" label="Teléfono" type="tel" :value="$patient->phone" required />
            <x-field name="email" label="Correo" type="email" :value="$patient->email" hint="Para recordatorios, resultados y acceso al portal." />
            <x-field name="city" label="Ciudad" :value="$patient->city" />
            <x-field name="address" label="Dirección" :value="$patient->address" />
            <x-field name="insurer_id" label="Aseguradora" type="select">
                <option value="">Particular</option>
                @foreach ($insurers as $insurer)
                    <option value="{{ $insurer->id }}" @selected((string) old('insurer_id', $patient->insurer_id) === (string) $insurer->id)>{{ $insurer->name }}</option>
                @endforeach
            </x-field>
            <x-field name="civil_status" label="Estado civil" type="select">
                <option value="">Sin dato</option>
                @foreach (\App\Enums\CivilStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('civil_status', $patient->civil_status?->value) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </x-field>
            <x-field name="occupation" label="Ocupación" :value="$patient->occupation" class="sm:col-span-2" />
        </fieldset>

        <div class="flex gap-2">
            <button class="btn-primary">{{ $editing ? 'Guardar cambios' : 'Registrar paciente' }}</button>
            <a href="{{ $editing ? route('patients.show', $patient) : route('patients.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
