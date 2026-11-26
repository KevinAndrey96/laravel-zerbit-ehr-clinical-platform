<x-layouts.site :title="'Ingreso del personal · '.config('app.name')">
    <section class="bg-gradient-to-b from-brand-50 to-white px-4 py-16">
        <div class="card mx-auto max-w-md p-8">
            <h1 class="text-xl font-semibold text-slate-900">Ingreso del personal</h1>
            <p class="mt-1 text-sm text-slate-600">Fisioterapeutas y personal administrativo de la clínica.</p>

            <form method="post" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf
                <x-field name="email" label="Correo" type="email" autocomplete="username" required autofocus />
                <x-field name="password" label="Contraseña" type="password" autocomplete="current-password" required />
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Mantener la sesión en este equipo
                </label>
                <button class="btn-primary w-full">Ingresar</button>
            </form>
            <p class="mt-6 text-xs text-slate-500">¿Olvidaste la contraseña? La coordinación de la clínica puede asignarte una nueva.</p>
            <p class="mt-2 text-xs text-slate-500">¿Eres paciente? <a href="{{ route('portal.login') }}" class="link">Entra al portal del paciente</a>.</p>
        </div>
    </section>
</x-layouts.site>
