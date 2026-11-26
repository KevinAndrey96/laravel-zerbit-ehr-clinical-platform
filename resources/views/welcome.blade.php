<x-layouts.site :title="config('app.name').' · Fisioterapia y laboratorio clínico'" :index="true">
    <section class="bg-gradient-to-b from-brand-50 to-white px-4 py-16">
        <div class="mx-auto grid max-w-6xl items-center gap-10 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold tracking-wide text-brand-700 uppercase">Fisioterapia y laboratorio clínico</p>
                <h1 class="mt-2 text-4xl font-semibold tracking-tight text-slate-900">Tu tratamiento y tus resultados, en un solo lugar</h1>
                <p class="mt-4 text-lg text-slate-600">Historia clínica electrónica con firma del paciente en cada sesión, agenda de citas con recordatorios y resultados de laboratorio que descargas desde casa.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('portal.login') }}" class="btn-primary px-5 py-2.5">Consultar mis resultados</a>
                    <a href="{{ route('login') }}" class="btn-secondary px-5 py-2.5">Ingreso del personal</a>
                </div>
            </div>
            <ul class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['Historia clínica', 'Valoración inicial, diagnóstico CIE-10 y evolución de cada sesión con firma del paciente.'],
                    ['Sesiones autorizadas', 'Control de las sesiones que aprobó tu aseguradora para que nada se quede por fuera.'],
                    ['Laboratorio', 'Seguimiento de tu muestra desde la toma hasta el resultado, con aviso por correo.'],
                    ['Agenda', 'Citas sin cruces de horario y recordatorio el día anterior.'],
                ] as [$heading, $text])
                    <li class="card p-5">
                        <h2 class="font-semibold text-brand-800">{{ $heading }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layouts.site>
