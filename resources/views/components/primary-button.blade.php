<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full px-5 py-3 text-sm font-semibold uppercase tracking-[0.12em] transition duration-200 ease-out neon-btn hover:shadow-[0_0_26px_rgba(124,58,237,0.45)]']) }}>
    {{ $slot }}
</button>
