<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                <header class="admin-header">
                    <div><h2>{{ $venue->name }}</h2><p>{{ $venue->location }}</p></div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.venues.index') }}" class="ad-btn">Back</a>
                        <a href="{{ route('admin.venues.edit', $venue) }}" class="ad-btn ad-btn-primary">Edit</a>
                    </div>
                </header>
                <section class="ad-card">
                    <p class="ad-card-label">Capacity</p>
                    <p class="ad-card-value">{{ number_format($venue->capacity) }}</p>


                    <p class="ad-card-label">Total Concerts</p>
                    <p>{{ $venue->concerts->count() }}</p>

                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.theme-script')
</x-app-layout>
