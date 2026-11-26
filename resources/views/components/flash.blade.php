@if (session('status'))
    <div role="status" class="mb-4 flex items-start justify-between gap-4 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900">
        <span>{{ session('status') }}</span>
        <button type="button" data-dismiss class="text-brand-700" aria-label="Cerrar">&times;</button>
    </div>
@endif
@if ($errors->hasAny(['delete', 'active']))
    <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ $errors->first('delete') ?: $errors->first('active') }}
    </div>
@endif
