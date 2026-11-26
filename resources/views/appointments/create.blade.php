<x-layouts.app title="Agendar cita">
    <x-page-header title="Agendar cita" subtitle="Lunes a sábado entre las 6:00 a. m. y las 8:00 p. m. El sistema evita cruces de horario."
        :back="[route('appointments.index'), 'Agenda']" />

    <form method="post" action="{{ route('appointments.store') }}" class="card grid max-w-3xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        <x-field name="patient_id" label="Paciente" type="select" required class="sm:col-span-2">
            @if ($patient === null)
                <option value="">Selecciona un paciente</option>
            @endif
            @foreach ($patients as $option)
                <option value="{{ $option->id }}" @selected((string) old('patient_id', $patient?->id) === (string) $option->id)>{{ $option->fullName() }} · {{ $option->document() }}</option>
            @endforeach
        </x-field>
        <x-field name="therapist_id" label="Fisioterapeuta" type="select" required>
            <option value="">Selecciona</option>
            @foreach ($therapists as $therapist)
                <option value="{{ $therapist->id }}" @selected((string) old('therapist_id') === (string) $therapist->id)>{{ $therapist->fullName() }}</option>
            @endforeach
        </x-field>
        <x-field name="duration" label="Duración" type="select" required>
            @foreach ($durations as $minutes)
                <option value="{{ $minutes }}" @selected((int) old('duration', 45) === $minutes)>{{ $minutes }} minutos</option>
            @endforeach
        </x-field>
        <x-field name="starts_at" label="Fecha y hora" type="datetime-local" :value="$start->format('Y-m-d\TH:i')" step="900" required />
        <x-field name="notes" label="Notas para el fisioterapeuta" :value="null" placeholder="Ej.: trae resonancia" />
        <div class="sm:col-span-2 flex gap-2">
            <button class="btn-primary">Agendar</button>
            <a href="{{ route('appointments.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
