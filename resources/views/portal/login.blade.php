<x-layouts.site :title="'Portal del paciente · '.config('app.name')" description="Consulta y descarga tus resultados de laboratorio y revisa tus próximas citas.">
    <section class="bg-gradient-to-b from-brand-50 to-white px-4 py-16">
        <div class="card mx-auto max-w-md p-8">
            <h1 class="text-xl font-semibold text-slate-900">Portal del paciente</h1>
            <p class="mt-1 text-sm text-slate-600">Escribe tu documento y el correo que registraste en la clínica. Te enviaremos un enlace de acceso que vence en {{ \App\Actions\SendPortalLink::MINUTES }} minutos.</p>

            @if (session('status'))
                <div role="status" class="mt-4 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900">{{ session('status') }}</div>
            @endif

            <form method="post" action="{{ route('portal.link') }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid grid-cols-[7rem_1fr] gap-2">
                    <x-field name="document_type" label="Documento" type="select" required>
                        @foreach (\App\Enums\DocumentType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('document_type', 'CC') === $type->value)>{{ $type->value }}</option>
                        @endforeach
                    </x-field>
                    <x-field name="document_number" label="Número" autocomplete="off" inputmode="text" required />
                </div>
                <x-field name="email" label="Correo" type="email" autocomplete="email" required />
                <button class="btn-primary w-full">Enviarme el enlace</button>
            </form>
            <p class="mt-6 text-xs text-slate-500">¿Cambiaste de correo? Actualízalo en la recepción de la clínica.</p>
        </div>
    </section>
</x-layouts.site>
