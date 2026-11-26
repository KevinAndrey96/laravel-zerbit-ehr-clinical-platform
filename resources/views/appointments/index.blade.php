@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $query = fn ($date) => array_filter(['dia' => $date->toDateString(), 'fisioterapeuta' => $therapistId ?: null]);
@endphp
<x-layouts.app title="Agenda">
    <x-page-header :title="$user->isTherapist() ? 'Mi agenda' : 'Agenda'" :subtitle="ucfirst($fmt->longDate($day))">
        <x-slot:actions>
            @can('create', \App\Models\Appointment::class)
                <a href="{{ route('appointments.create') }}" class="btn-primary">Agendar cita</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <form method="get" action="{{ route('appointments.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <a href="{{ route('appointments.index', $query($day->subDay())) }}" class="btn-secondary" aria-label="Día anterior">&larr;</a>
        <div>
            <label for="dia" class="label">Día</label>
            <input id="dia" name="dia" type="date" value="{{ $day->toDateString() }}" class="input">
        </div>
        @if ($user->isAdmin())
            <div>
                <label for="fisioterapeuta" class="label">Fisioterapeuta</label>
                <select id="fisioterapeuta" name="fisioterapeuta" class="input">
                    <option value="">Todos</option>
                    @foreach ($therapists as $therapist)
                        <option value="{{ $therapist->id }}" @selected($therapistId === $therapist->id)>{{ $therapist->fullName() }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button class="btn-secondary">Ver</button>
        <a href="{{ route('appointments.index', $query($day->addDay())) }}" class="btn-secondary" aria-label="Día siguiente">&rarr;</a>
        @unless ($day->isToday())
            <a href="{{ route('appointments.index', array_filter(['fisioterapeuta' => $therapistId ?: null])) }}" class="link self-center text-sm">Hoy</a>
        @endunless
    </form>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    @if ($appointments->isEmpty())
        <x-empty>No hay citas para este día.</x-empty>
    @else
        <ul class="space-y-3">
            @foreach ($appointments as $appointment)
                <li class="card flex flex-wrap items-start justify-between gap-4 p-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900"><span class="tabular-nums">{{ $fmt->time($appointment->starts_at) }} a {{ $fmt->time($appointment->ends_at) }}</span></p>
                        <p class="text-sm"><a href="{{ route('patients.show', $appointment->patient) }}" class="link">{{ $appointment->patient->fullName() }}</a> · {{ $appointment->patient->phone }}</p>
                        @if ($user->isAdmin())
                            <p class="text-xs text-slate-500">{{ $appointment->therapist->fullName() }}</p>
                        @endif
                        @if ($appointment->notes)
                            <p class="mt-1 text-xs text-slate-600">{{ $appointment->notes }}</p>
                        @endif
                        @if ($appointment->cancellation_reason)
                            <p class="mt-1 text-xs text-rose-700">Motivo: {{ $appointment->cancellation_reason }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <x-badge :tone="$appointment->status->tone()">{{ $appointment->status->label() }}</x-badge>
                        @can('update', $appointment)
                            @if ($appointment->status->transitions() !== [])
                                <form method="post" action="{{ route('appointments.status', $appointment) }}" class="flex flex-wrap items-center justify-end gap-2">
                                    @csrf
                                    @method('patch')
                                    <label for="estado-{{ $appointment->id }}" class="sr-only">Nuevo estado</label>
                                    <select id="estado-{{ $appointment->id }}" name="status" class="input w-auto py-1 text-xs" required>
                                        <option value="" selected disabled>Cambiar estado</option>
                                        @foreach ($appointment->status->transitions() as $next)
                                            <option value="{{ $next->value }}">{{ $next->label() }}</option>
                                        @endforeach
                                    </select>
                                    <label for="motivo-{{ $appointment->id }}" class="sr-only">Motivo de cancelación</label>
                                    <input id="motivo-{{ $appointment->id }}" name="cancellation_reason" class="input w-48 py-1 text-xs" placeholder="Motivo si cancelas">
                                    <button class="btn-secondary py-1 text-xs">Actualizar</button>
                                </form>
                            @endif
                        @endcan
                        @can('remind', $appointment)
                            @if ($appointment->status->holdsSlot() && $appointment->patient->email)
                                <form method="post" action="{{ route('appointments.remind', $appointment) }}">
                                    @csrf
                                    <button class="text-xs font-medium text-brand-700 hover:underline">{{ $appointment->reminder_sent_at ? 'Reenviar recordatorio' : 'Enviar recordatorio' }}</button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
