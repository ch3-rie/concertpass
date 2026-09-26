@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-white/10 bg-white/5 text-white focus:border-violet-500 focus:ring-violet-500 rounded-xl shadow-sm']) !!}>