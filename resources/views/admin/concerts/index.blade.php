<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div><h2>Concert Catalog</h2><p>Manage event listings, schedules, and publication readiness.</p></div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.concerts.create') }}" class="ad-btn ad-btn-primary">Add Concert</a>
                    </div>
                </header>
                <section class="ad-card">
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead><tr><th>Title</th><th>Artist</th><th>Venue</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($concerts as $concert)
                                    <tr>
                                        <td>{{ $concert->title }}</td>
                                        <td>{{ $concert->artist }}</td>
                                        <td>{{ optional($concert->venue)->name }}</td>
                                        <td>{{ $concert->date?->format('M d, Y') }}</td>
                                        <td style="display:flex; gap:.45rem;">
                                            <a href="{{ route('admin.concerts.show', $concert) }}" class="ad-btn">View</a>
                                            <a href="{{ route('admin.concerts.edit', $concert) }}" class="ad-btn">Edit</a>
                                            @if($soldConcertIds->contains($concert->id))
                                                <span class="ad-btn" style="opacity:0.55; cursor:not-allowed; border:1px solid rgba(148,163,184,0.35);" title="Tickets have been sold; this concert cannot be deleted.">Delete</span>
                                            @else
                                            <form method="POST" action="{{ route('admin.concerts.destroy', $concert) }}" data-confirm="true" data-confirm-message="Are you sure to delete this concert?">
                                                @csrf @method('DELETE')
                                                <button class="ad-btn ad-btn-logout" type="submit" data-loading-text="Deleting...">Delete</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5">No concerts found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:.8rem;">{{ $concerts->links() }}</div>
                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.confirm-modal')
    @include('admin.partials.theme-script')
</x-app-layout>
