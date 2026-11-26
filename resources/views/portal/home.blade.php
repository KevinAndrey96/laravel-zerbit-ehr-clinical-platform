<x-layouts.site :title="'Mis resultados · '.config('app.name')" :nav="false">
    <section class="mx-auto max-w-4xl px-4 py-10">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">Portal del paciente</p>
                <h1 class="text-2xl font-semibold">Hola, {{ $patient->first_name }}</h1>
            </div>
            <form method="post" action="{{ route('portal.logout') }}">
                @csrf
                <button class="btn-secondary">Cerrar sesión</button>
            </form>
        </div>

        <section class="card mb-6" aria-labelledby="resultados">
            <h2 id="resultados" class="border-b border-slate-200 px-5 py-3 font-semibold">Resultados de laboratorio</h2>
            @forelse ($results as $sample)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 text-sm last:border-0">
                    <div>
                        <p class="font-medium">{{ $sample->exam }}</p>
                        <p class="text-xs text-slate-500">Muestra {{ $sample->code }} · tomada el {{ $fmt->date($sample->taken_on) }}</p>
                    </div>
                    <a href="{{ route('portal.download', $sample) }}" class="btn-primary py-1.5">Descargar PDF</a>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-600">Aún no tienes resultados disponibles.</p>
            @endforelse
        </section>

        @if ($pending->isNotEmpty())
            <section class="card mb-6" aria-labelledby="en-proceso">
                <h2 id="en-proceso" class="border-b border-slate-200 px-5 py-3 font-semibold">En proceso</h2>
                @foreach ($pending as $sample)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 text-sm last:border-0">
                        <span>{{ $sample->exam }} · {{ $fmt->date($sample->taken_on) }}</span>
                        <x-badge :tone="$sample->status->tone()">{{ $sample->status->label() }}</x-badge>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="card" aria-labelledby="citas">
            <h2 id="citas" class="border-b border-slate-200 px-5 py-3 font-semibold">Próximas citas</h2>
            @forelse ($appointments as $appointment)
                <div class="border-b border-slate-100 px-5 py-3 text-sm last:border-0">
                    <p class="font-medium">{{ ucfirst($fmt->longDate($appointment->starts_at)) }}, {{ $fmt->time($appointment->starts_at) }}</p>
                    <p class="text-xs text-slate-500">Fisioterapia con {{ $appointment->therapist->fullName() }}</p>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-600">No tienes citas programadas.</p>
            @endforelse
        </section>
        <p class="mt-6 text-xs text-slate-500">Por seguridad la sesión se cierra tras 30 minutos sin actividad.</p>
    </section>
</x-layouts.site>
