<x-layouts.app title="Mi día">
    <x-page-header title="Mi día" :subtitle="ucfirst($fmt->longDate($today))">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn-primary">Buscar paciente</a>
        </x-slot:actions>
    </x-page-header>

    @unless ($hasSignature)
        <div role="status" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Aún no registras tu firma. <a href="{{ route('signature.edit') }}" class="font-semibold underline">Regístrala ahora</a> para que aparezca en las historias clínicas impresas.
        </div>
    @endunless

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card lg:col-span-2" aria-labelledby="mis-citas">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h2 id="mis-citas" class="font-semibold">Mis citas de hoy</h2>
                <a href="{{ route('appointments.index') }}" class="link text-sm">Ver agenda</a>
            </div>
            @if ($agenda->isEmpty())
                <x-empty class="m-4">No tienes citas para hoy.</x-empty>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($agenda as $appointment)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                            <div>
                                <p class="font-medium text-slate-900"><span class="tabular-nums">{{ $fmt->time($appointment->starts_at) }}</span> · {{ $appointment->patient->fullName() }}</p>
                                @if ($appointment->notes)
                                    <p class="text-xs text-slate-500">{{ $appointment->notes }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <x-badge :tone="$appointment->status->tone()">{{ $appointment->status->label() }}</x-badge>
                                <a href="{{ route('patients.show', $appointment->patient) }}" class="link">Abrir</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="card" aria-labelledby="sin-firma">
            <h2 id="sin-firma" class="border-b border-slate-200 px-4 py-3 font-semibold">Sesiones sin firma del paciente</h2>
            @forelse ($unsigned as $evolution)
                <a href="{{ route('histories.show', $evolution->clinicalHistory) }}#sesion-{{ $evolution->session_number }}" class="block border-b border-slate-100 px-4 py-3 text-sm last:border-0 hover:bg-slate-50">
                    <span class="font-medium">{{ $evolution->clinicalHistory->patient->fullName() }}</span>
                    <span class="block text-xs text-slate-500">Sesión {{ $evolution->session_number }} · {{ $fmt->date($evolution->attended_on) }}</span>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-slate-600">Todas tus sesiones están firmadas.</p>
            @endforelse
        </section>
    </div>

    <section class="mt-6" aria-labelledby="mis-tratamientos">
        <h2 id="mis-tratamientos" class="mb-3 font-semibold">Mis tratamientos activos</h2>
        @if ($histories->isEmpty())
            <x-empty>No tienes tratamientos abiertos.</x-empty>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($histories as $history)
                    @php($plan = $history->plan())
                    <a href="{{ route('histories.show', $history) }}" class="card block p-4 hover:border-brand-300">
                        <p class="font-medium text-slate-900">{{ $history->patient->fullName() }}</p>
                        <p class="text-xs text-slate-500">{{ $history->diagnosis_code }} · {{ $history->diagnosis }}</p>
                        <x-progress :plan="$plan" class="mt-3" />
                        <p class="mt-1 text-xs text-slate-600">{{ $plan->held }} de {{ $plan->authorized }} sesiones</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
