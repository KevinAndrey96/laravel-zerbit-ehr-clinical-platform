@props(['plan'])
<progress {{ $attributes->merge(['class' => 'block h-2 w-full overflow-hidden rounded-full [&::-moz-progress-bar]:bg-brand-500 [&::-webkit-progress-bar]:bg-slate-100 [&::-webkit-progress-value]:bg-brand-500']) }}
    max="{{ $plan->authorized }}" value="{{ $plan->held }}" aria-label="Sesiones realizadas">{{ $plan->held }} de {{ $plan->authorized }}</progress>
