@props(['title'])
@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $links = $user->isAdmin()
        ? [
            ['dashboard', 'Resumen', 'panel'],
            ['appointments.index', 'Agenda', 'agenda*'],
            ['patients.index', 'Pacientes', 'pacientes*'],
            ['lab.index', 'Laboratorio', 'laboratorio*'],
            ['admin.staff.index', 'Personal', 'admin/personal*'],
            ['admin.insurers.index', 'Aseguradoras', 'admin/aseguradoras*'],
        ]
        : [
            ['dashboard', 'Mi día', 'panel'],
            ['appointments.index', 'Mi agenda', 'agenda*'],
            ['histories.index', 'Historias', 'historias*'],
            ['patients.index', 'Pacientes', 'pacientes*'],
            ['signature.edit', 'Mi firma', 'mi-firma'],
        ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:rounded focus:bg-white focus:px-3 focus:py-2">Saltar al contenido</a>
    <header class="bg-brand-800 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold">
                <x-logo class="size-8" />
                <span>{{ config('app.name') }}</span>
            </a>
            <button type="button" class="rounded p-2 lg:hidden" data-menu-toggle aria-controls="main-menu" aria-expanded="false" aria-label="Abrir menú">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="hidden items-center gap-3 text-sm lg:flex">
                <span class="text-brand-100">{{ $user->fullName() }} · {{ $user->role->label() }}</span>
                <a href="{{ route('password.edit') }}" class="rounded px-2 py-1 hover:bg-brand-700">Contraseña</a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded px-2 py-1 hover:bg-brand-700">Salir</button>
                </form>
            </div>
        </div>
        <nav id="main-menu" class="hidden border-t border-brand-700 lg:block" aria-label="Principal">
            <ul class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-2 text-sm lg:flex-row lg:overflow-x-auto">
                @foreach ($links as [$route, $label, $pattern])
                    <li>
                        <a href="{{ route($route) }}" @class([
                            'block whitespace-nowrap rounded-md px-3 py-1.5',
                            'bg-white/15 font-semibold' => request()->is($pattern),
                            'text-brand-100 hover:bg-white/10' => ! request()->is($pattern),
                        ]) @if (request()->is($pattern)) aria-current="page" @endif>{{ $label }}</a>
                    </li>
                @endforeach
                <li class="border-t border-brand-700 pt-2 lg:hidden">
                    <a href="{{ route('password.edit') }}" class="block rounded-md px-3 py-1.5 text-brand-100">Cambiar contraseña</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="block w-full rounded-md px-3 py-1.5 text-left text-brand-100">Salir</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <main id="contenido" class="mx-auto max-w-7xl px-4 py-6">
        <x-flash />
        {{ $slot }}
    </main>
</body>
</html>
