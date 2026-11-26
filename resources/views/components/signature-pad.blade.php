@props(['action', 'method' => 'post', 'label' => 'Guardar firma', 'bag' => 'signature', 'id' => 'firma'])
<form method="post" action="{{ $action }}" data-signature-form {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @csrf
    @if ($method !== 'post')
        @method($method)
    @endif
    <input type="hidden" name="signature" data-signature-input>
    <canvas id="{{ $id }}" width="600" height="200" data-signature-canvas
        class="block h-[150px] w-full max-w-[480px] touch-none rounded-lg border border-dashed border-slate-400 bg-white"
        aria-label="Recuadro para firmar con el dedo o el mouse"></canvas>
    @error('signature', $bag)
        <p class="text-xs text-red-700">{{ $message }}</p>
    @enderror
    <noscript><p class="text-xs text-red-700">Para firmar necesitas activar JavaScript en el navegador.</p></noscript>
    <div class="flex gap-2">
        <button type="button" class="btn-secondary" data-signature-clear>Borrar</button>
        <button type="submit" class="btn-primary" data-signature-submit disabled>{{ $label }}</button>
    </div>
</form>
