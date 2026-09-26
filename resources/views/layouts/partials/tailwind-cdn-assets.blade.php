<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    poppins: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                },
            },
        },
    };
</script>

@php
    $appCss = file_get_contents(resource_path('css/app.css')) ?: '';
    $appCss = str_replace(
        [
            "@import './admin-dashboard.css';",
            "@import './user-dashboard.css';",
            '@tailwind base;',
            '@tailwind components;',
            '@tailwind utilities;',
        ],
        '',
        $appCss
    );
@endphp

<style>{!! file_get_contents(resource_path('css/admin-dashboard.css')) !!}</style>
<style>{!! file_get_contents(resource_path('css/user-dashboard.css')) !!}</style>
<style>{!! $appCss !!}</style>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script>{!! file_get_contents(resource_path('js/app-standalone.js')) !!}</script>
