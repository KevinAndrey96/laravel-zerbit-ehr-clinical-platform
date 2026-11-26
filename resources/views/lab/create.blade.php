<x-layouts.app title="Registrar muestra">
    <x-page-header title="Registrar muestra" subtitle="Al guardar se asigna el código para rotular el recipiente." :back="[route('lab.index'), 'Laboratorio']" />

    <form method="post" action="{{ route('lab.store') }}" class="card grid max-w-3xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        <x-field name="patient_id" label="Paciente" type="select" required class="sm:col-span-2">
            @if ($patient === null)
                <option value="">Selecciona un paciente</option>
            @endif
            @foreach ($patients as $option)
                <option value="{{ $option->id }}" @selected((string) old('patient_id', $patient?->id) === (string) $option->id)>{{ $option->fullName() }} · {{ $option->document() }}</option>
            @endforeach
        </x-field>
        <x-field name="exam" label="Examen" placeholder="Cuadro hemático" required />
        <x-field name="sample_type" label="Tipo de muestra" type="select" required>
            @foreach (\App\Enums\SampleType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('sample_type') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </x-field>
        <x-field name="taken_on" label="Fecha de toma" type="date" :value="$today" :max="$today->toDateString()" required />
        <x-field name="authorization_number" label="N.º de autorización" hint="Opcional, si la aseguradora lo exige." />
        <label class="flex items-center gap-2 text-sm text-slate-700 sm:col-span-2">
            <input type="hidden" name="notify_by_email" value="0">
            <input type="checkbox" name="notify_by_email" value="1" @checked(old('notify_by_email', '1') === '1') class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Avisar al paciente por correo cuando el resultado esté listo
        </label>
        <div class="flex gap-2 sm:col-span-2">
            <button class="btn-primary">Registrar muestra</button>
            <a href="{{ route('lab.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
