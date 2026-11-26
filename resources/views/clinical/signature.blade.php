<x-layouts.app title="Mi firma">
    <x-page-header title="Mi firma profesional" subtitle="Se imprime junto a tu nombre y registro en las historias clínicas en PDF." />

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card p-6" aria-labelledby="firma-actual">
            <h2 id="firma-actual" class="mb-3 font-semibold">Firma registrada</h2>
            @if ($current)
                <img src="{{ $current }}" alt="Tu firma registrada" class="h-24 w-auto rounded border border-slate-200 bg-white p-2">
            @else
                <p class="text-sm text-slate-600">Aún no has registrado tu firma.</p>
            @endif
        </section>
        <section class="card p-6" aria-labelledby="nueva-firma">
            <h2 id="nueva-firma" class="mb-3 font-semibold">{{ $current ? 'Reemplazar firma' : 'Registrar firma' }}</h2>
            <x-signature-pad :action="route('signature.update')" method="put" label="Guardar mi firma" />
        </section>
    </div>
</x-layouts.app>
