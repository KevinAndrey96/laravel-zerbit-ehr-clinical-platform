@php($editing = $history->exists)
<x-layouts.app :title="$editing ? 'Editar valoración' : 'Nueva historia clínica'">
    <x-page-header :title="$editing ? 'Editar valoración inicial' : 'Nueva historia clínica'"
        :subtitle="$patient->fullName().' · '.$patient->document().' · '.$patient->age()->label()"
        :back="$editing ? [route('histories.show', $history), 'Historia clínica'] : [route('patients.show', $patient), 'Ficha del paciente']" />

    <form method="post" action="{{ $editing ? route('histories.update', $history) : route('histories.store', $patient) }}" class="space-y-6">
        @csrf
        @if ($editing)
            @method('put')
        @endif

        <fieldset class="card grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <legend class="sr-only">Autorización</legend>
            <h2 class="text-sm font-semibold text-slate-900 sm:col-span-2 lg:col-span-4">Apertura y autorización</h2>
            <x-field name="opened_on" label="Fecha de apertura" type="date" :value="$history->opened_on" :max="now()->toDateString()" required />
            <x-field name="authorized_sessions" label="Sesiones autorizadas" type="number" :value="$history->authorized_sessions" min="1" max="60" required />
            <x-field name="authorization_number" label="N.º de autorización" :value="$history->authorization_number" hint="Lo entrega la aseguradora." />
            <x-field name="copayment_cop" label="Copago por sesión" :value="$history->copayment_cop" inputmode="numeric" suffix="COP" />
        </fieldset>

        <fieldset class="card grid gap-4 p-6 sm:grid-cols-2">
            <legend class="sr-only">Diagnóstico</legend>
            <h2 class="text-sm font-semibold text-slate-900 sm:col-span-2">Motivo y diagnóstico</h2>
            <x-field name="reason" label="Motivo de consulta" type="textarea" :value="$history->reason" rows="3" class="sm:col-span-2" required />
            <x-field name="diagnosis_code" label="Código CIE-10" :value="$history->diagnosis_code" placeholder="M54.5" autocomplete="off" required />
            <x-field name="diagnosis" label="Diagnóstico médico" :value="$history->diagnosis" placeholder="Lumbago no especificado" required />
            <x-field name="physio_diagnosis" label="Diagnóstico fisioterapéutico" type="textarea" :value="$history->physio_diagnosis" rows="3" required />
            <x-field name="objective" label="Objetivo del tratamiento" type="textarea" :value="$history->objective" rows="3" required />
        </fieldset>

        <fieldset class="card grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3">
            <legend class="sr-only">Antecedentes</legend>
            <h2 class="text-sm font-semibold text-slate-900 sm:col-span-2 lg:col-span-3">Antecedentes</h2>
            @foreach (\App\Models\ClinicalHistory::BACKGROUND as $field => $label)
                <x-field :name="$field" :label="$label" type="textarea" :value="$history->getAttribute($field)" rows="2" placeholder="Niega" />
            @endforeach
        </fieldset>

        <fieldset class="card grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3">
            <legend class="sr-only">Valoración</legend>
            <h2 class="text-sm font-semibold text-slate-900 sm:col-span-2 lg:col-span-3">Valoración inicial</h2>
            <x-field name="pain_scale" label="Dolor (EVA 0 a 10)" type="number" :value="$history->pain_scale" min="0" max="10" />
            <x-field name="pain_notes" label="Características del dolor" type="textarea" :value="$history->pain_notes" rows="2" />
            <x-field name="fall_risk" label="Riesgo de caída" type="select">
                <option value="">Sin valorar</option>
                @foreach (\App\Enums\FallRisk::cases() as $risk)
                    <option value="{{ $risk->value }}" @selected(old('fall_risk', $history->fall_risk?->value) === $risk->value)>{{ $risk->label() }}</option>
                @endforeach
            </x-field>
            @foreach (\App\Models\ClinicalHistory::ASSESSMENT as $field => $label)
                <x-field :name="$field" :label="$label" type="textarea" :value="$history->getAttribute($field)" rows="2" />
            @endforeach
        </fieldset>

        <div class="flex gap-2">
            <button class="btn-primary">{{ $editing ? 'Guardar valoración' : 'Abrir historia clínica' }}</button>
            <a href="{{ $editing ? route('histories.show', $history) : route('patients.show', $patient) }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
