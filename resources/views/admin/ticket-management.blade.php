<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                <header class="admin-header">
                    <div>
                        <h2>Ticket Management</h2>
                        <p>Oversee ticket allocation, sell-through rates, and remaining inventory by event.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="ad-btn ad-btn-primary">Dashboard</a>
                    </div>
                </header>

                <section class="ad-card">
                    <h3 class="ad-panel-title">Ticket Inventory</h3>
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead>
                                <tr>
                                    <th>Concert</th>
                                    <th>Allocation</th>
                                    <th>Sold</th>
                                    <th>Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ticketStats as $stat)
                                    @php($allocation = $stat->allocated_seats)
                                    @php($remaining = max($allocation - $stat->sold_seats, 0))
                                    <tr>
                                        <td>{{ $stat->title }}</td>
                                        <td>{{ number_format($allocation) }}</td>
                                        <td>{{ number_format($stat->sold_seats) }}</td>
                                        <td>{{ number_format($remaining) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No concert seat data found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
</x-app-layout>
