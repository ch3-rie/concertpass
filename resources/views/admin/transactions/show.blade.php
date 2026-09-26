<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                <header class="admin-header">
                    <div>
                        <h2>Transaction Details</h2>
                        <p>Complete transaction context with safe, masked payment method visibility.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                        <a href="{{ route('admin.transactions.index') }}" class="ad-btn">Back to Transactions</a>
                    </div>
                </header>

                @php($displayDate = $transaction->paid_at ?? $transaction->created_at)
                @php($rawCardValue = trim((string) $transaction->payment_method))
                @php($cardDigits = preg_replace('/\D+/', '', $rawCardValue) ?? '')
                @php($formattedCardValue = $rawCardValue)
                @php($maskedCardValue = $rawCardValue)
                @if($cardDigits !== '')
                    @php($formattedCardValue = trim(chunk_split($cardDigits, 4, ' ')))
                    @php($maskedDigits = str_repeat('*', max(0, strlen($cardDigits) - 4)) . substr($cardDigits, -4))
                    @php($maskedCardValue = trim(chunk_split($maskedDigits, 4, ' ')))
                @endif

                <section class="ad-card ad-crud-card">
                    <h3 class="ad-crud-title">#TXN-{{ str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT) }}</h3>
                    <div class="ad-detail-list">
                        <article class="ad-detail-item">
                            <p class="ad-label">User</p>
                            <p class="value">{{ optional(optional($transaction->booking)->user)->name ?? 'Deleted User' }}</p>
                            <p class="ad-muted-note">{{ optional(optional($transaction->booking)->user)->email ?? 'No email available' }}</p>
                        </article>
                        <article class="ad-detail-item">
                            <p class="ad-label">Related Event</p>
                            <p class="value">{{ optional(optional($transaction->booking)->concert)->title ?? 'Deleted Event' }}</p>
                            <p class="ad-muted-note">Booking #{{ $transaction->booking_id }}</p>
                        </article>
                        <article class="ad-detail-item">
                            <p class="ad-label">Amount Paid</p>
                            <p class="value">₱{{ number_format((float) $transaction->amount, 2) }}</p>
                        </article>
                        <article class="ad-detail-item">
                            <p class="ad-label">Card Details</p>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <p class="value" id="cardValueText"
                                    data-full="{{ $formattedCardValue }}"
                                    data-masked="{{ $maskedCardValue }}"
                                    style="margin: 0;">
                                    {{ $maskedCardValue ?: '-' }}
                                </p>
                                <button type="button" class="ad-btn ad-btn-compact" id="toggleCardBtn">Show</button>
                            </div>
                        </article>
                        <article class="ad-detail-item">
                            <p class="ad-label">Payment Status</p>
                            <p class="value">
                                <span class="ad-status {{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'pending' ? 'pending' : 'failed') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </p>
                        </article>
                        <article class="ad-detail-item">
                            <p class="ad-label">Transaction Date</p>
                            <p class="value">{{ optional($displayDate)->format('M d, Y h:i A') ?? '-' }}</p>
                        </article>
                    </div>
                </section>

                <section class="ad-card ad-section-gap">
                    <h3 class="ad-panel-title">Tickets Ordered</h3>
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead>
                                <tr>
                                    <th>Ticket Type</th>
                                    <th>Seat</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(optional($transaction->booking)->tickets ?? [] as $ticket)
                                    <tr>
                                        <td>{{ $ticket->ticket_type ?? '-' }}</td>
                                        <td>
                                            @if($ticket->seat)
                                                {{ $ticket->seat->section }} - Seat {{ $ticket->seat->seat_number }}
                                            @else
                                                Auto-assigned / N/A
                                            @endif
                                        </td>
                                        <td>₱{{ number_format((float) $ticket->price_at_purchase, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No ticket records found for this transaction.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </section>

    <script>
        (function () {
            const cardValue = document.getElementById('cardValueText');
            const toggleBtn = document.getElementById('toggleCardBtn');

            if (!cardValue || !toggleBtn) {
                return;
            }

            let isVisible = false;

            toggleBtn.addEventListener('click', function () {
                isVisible = !isVisible;
                cardValue.textContent = isVisible
                    ? (cardValue.dataset.full || '-')
                    : (cardValue.dataset.masked || '-');
                toggleBtn.textContent = isVisible ? 'Hide' : 'Show';
            });
        })();
    </script>

    @include('admin.partials.theme-script')
</x-app-layout>
