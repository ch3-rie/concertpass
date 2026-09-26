<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div>
                        <h2>Manage Transactions</h2>
                        <p>Monitor payment records separately from user management with searchable and sortable data.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                    </div>
                </header>

                <section class="ad-card">
                    <h3 class="ad-panel-title">Filters</h3>
                    <form method="GET" action="{{ route('admin.transactions.index') }}" class="ad-filter-grid">
                        <div class="ad-field">
                            <label class="ad-label" for="search">Search</label>
                            <input
                                id="search"
                                name="search"
                                class="ad-input"
                                value="{{ $filters['search'] }}"
                                placeholder="Transaction ID, user name, or email">
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="status">Status</label>
                            <select id="status" name="status" class="ad-select">
                                <option value="">All</option>
                                <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                                <option value="paid" @selected($filters['status'] === 'paid')>Paid</option>
                                <option value="failed" @selected($filters['status'] === 'failed')>Failed</option>
                            </select>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="sort">Sort by date</label>
                            <select id="sort" name="sort" class="ad-select">
                                <option value="newest" @selected($filters['sort'] === 'newest')>Newest to Oldest</option>
                                <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest to Newest</option>
                            </select>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="date_from">Date From</label>
                            <input id="date_from" name="date_from" type="date" class="ad-input" value="{{ $filters['date_from'] }}">
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="date_to">Date To</label>
                            <input id="date_to" name="date_to" type="date" class="ad-input" value="{{ $filters['date_to'] }}">
                        </div>
                        <div class="ad-filter-actions">
                            <button type="submit" class="ad-btn ad-btn-primary">Apply</button>
                            <a href="{{ route('admin.transactions.index') }}" class="ad-btn">Reset</a>
                        </div>
                    </form>
                </section>

                <section class="ad-card ad-section-gap">
                    <h3 class="ad-panel-title">Transaction Records</h3>
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>User</th>
                                    <th>Event</th>
                                    <th>Amount Paid</th>
                                    <th>Card Number</th>
                                    <th>Status</th>
                                    <th>Transaction Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    @php($displayDate = $transaction->paid_at ?? $transaction->created_at)
                                    <tr>
                                        <td>#TXN-{{ str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <strong>{{ optional(optional($transaction->booking)->user)->name ?? 'Deleted User' }}</strong><br>
                                            <span class="ad-muted-note">{{ optional(optional($transaction->booking)->user)->email ?? 'No email' }}</span>
                                        </td>
                                        <td>
                                            {{ optional(optional($transaction->booking)->concert)->title ?? 'Deleted Event' }}<br>
                                            <span class="ad-muted-note">Booking #{{ $transaction->booking_id }}</span>
                                        </td>
                                        <td>₱{{ number_format((float) $transaction->amount, 2) }}</td>
                                        <td>{{ $transaction->masked_payment_method }}</td>
                                        <td>
                                            <span class="ad-status {{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'pending' ? 'pending' : 'failed') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($displayDate)->format('M d, Y h:i A') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('admin.transactions.show', $transaction) }}" class="ad-btn ad-btn-compact">View Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">No transactions found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 0.8rem;">
                        {{ $transactions->links() }}
                    </div>
                </section>
            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
</x-app-layout>
