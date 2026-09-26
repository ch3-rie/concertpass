<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div><h2>Create Venue</h2><p>Create a venue profile with validated location and capacity information.</p></div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.venues.index') }}" class="ad-btn">Back</a>
                    </div>
                </header>
                <section class="ad-card">
                    <h3 class="ad-panel-title">Venue Information</h3>
<form method="POST" action="{{ route('admin.venues.store') }}" enctype="multipart/form-data" class="ad-form-grid-3">
                        @csrf
                        <div class="ad-field">
                            <label class="ad-label" for="name">Venue Name</label>
                            <input class="ad-input" id="name" type="text" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="location">Location</label>
                            <input class="ad-input" id="location" type="text" name="location" value="{{ old('location') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="capacity">Capacity</label>
                            <input class="ad-input" id="capacity" type="number" name="capacity" value="{{ old('capacity') }}" min="1" required>
                        </div>

                        <div class="ad-field ad-field-full">
                            <div class="ad-actions-row">
                                <button class="ad-btn ad-btn-primary" type="submit">Create Venue</button>
                            </div>
                        </div>

                    </form>
                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.theme-script')
</x-app-layout>
