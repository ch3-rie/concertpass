@props(['value'])

<label {{ $attributes->merge(['class' => 'text-sm font-medium text-slate-300']) }}>
    {{ $value ?? $slot }}
</label>
