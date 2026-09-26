<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div><h2>Venue Registry</h2><p>Maintain venue profiles, location details, and capacity records.</p></div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.venues.create') }}" class="ad-btn ad-btn-primary">Add Venue</a>
                    </div>
                </header>
                <section class="ad-card">
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead><tr><th>Name</th><th>Location</th><th>Capacity</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($venues as $venue)
                                    <tr>
                                        <td>{{ $venue->name }}</td>
                                        <td>{{ $venue->location }}</td>
                                        <td>{{ number_format($venue->capacity) }}</td>
                                        <td style="display:flex; gap:.45rem;">
                                            <a href="{{ route('admin.venues.show', $venue) }}" class="ad-btn">View</a>
                                            <a href="{{ route('admin.venues.edit', $venue) }}" class="ad-btn">Edit</a>
                                            <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}" data-confirm="true" data-confirm-message="Are you sure to delete this venue?">
                                                @csrf @method('DELETE')
                                                <button class="ad-btn ad-btn-logout" type="submit" data-loading-text="Deleting...">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">No venues found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:.8rem;">{{ $venues->links() }}</div>
                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.confirm-modal')
    @include('admin.partials.theme-script')
</x-app-layout>
