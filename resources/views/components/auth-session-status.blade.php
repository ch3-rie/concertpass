@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-4 bg-green-500/10 text-green-700 font-medium text-sm rounded']) }}>
        {{ $status }}
    </div>
@endif
