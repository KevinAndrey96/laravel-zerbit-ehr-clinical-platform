@props(['tone' => 'slate'])
@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'sky' => 'bg-sky-50 text-sky-800 ring-sky-200',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'emerald' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'rose' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'brand' => 'bg-brand-50 text-brand-800 ring-brand-200',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset '.($tones[$tone] ?? $tones['slate'])]) }}>{{ $slot }}</span>
