@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-orange-500 text-start text-base font-medium text-white bg-orange-500/20 focus:outline-none focus:text-white focus:bg-orange-500/25 focus:border-orange-500 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-300 hover:text-white hover:bg-white/10 hover:border-orange-500/50 focus:outline-none focus:text-white focus:bg-white/10 focus:border-orange-500/50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
