@props(['title', 'description' => 'Clínica de fisioterapia y laboratorio clínico: agenda tu cita y consulta tus resultados en línea.', 'index' => false, 'nav' => true])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    @unless ($index)
        <meta name="robots" content="noindex">
    @endunless
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CO">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold text-brand-800">
                <x-logo class="size-9" />
                <span>{{ config('app.name') }}</span>
            </a>
            @if ($nav)
                <nav aria-label="Principal">
                    <ul class="flex items-center gap-2 text-sm">
                        <li><a href="{{ route('portal.login') }}" class="btn-secondary py-1.5">Soy paciente</a></li>
                        <li><a href="{{ route('login') }}" class="btn-primary py-1.5">Personal</a></li>
                    </ul>
                </nav>
            @endif
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-brand-900 text-sm text-brand-100">
        <div class="mx-auto flex max-w-6xl flex-wrap justify-between gap-4 px-4 py-6">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. Fisioterapia y laboratorio clínico.</p>
            <p>Tus datos de salud se tratan conforme a la Ley 1581 de 2012 y la Resolución 1995 de 1999.</p>
        </div>
    </footer>
</body>
</html>
