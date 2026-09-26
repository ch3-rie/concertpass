<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div style="margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; align-items: center;">
                <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
                    <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 1</p>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Choose Tickets</h3>
                </div>
                <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
                    <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 2</p>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Review Order</h3>
                </div>
                <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
                    <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 3</p>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Checkout</h3>
                </div>
                <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
                    <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 4</p>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Get Tickets</h3>
                </div>
            </div>
        </div>

        <div class="max-w-lg mx-auto">
            <div class="bg-slate-950/80 border border-white/10 rounded-[1.75rem] p-8 shadow-[0_30px_80px_rgba(0,0,0,0.45)]">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400 text-4xl font-bold">
                    ✓
                </div>
                <p class="text-center text-[10px] uppercase tracking-[0.35em] text-slate-500 mb-3">Payment complete</p>
                <h1 class="text-3xl font-black text-white mb-3 text-center">Your payment was successful</h1>
                <p class="text-sm text-slate-400 mb-8">Confirmation has been sent to <span class="font-semibold text-white">{{ $booking->user->email }}</span></p>

            <div class="space-y-3 mb-8">
                <div class="rounded-3xl bg-white/5 border border-white/10 px-4 py-3">
                    <p class="text-[10px] uppercase tracking-[0.35em] text-slate-500">Order number</p>
                    <p class="text-white font-semibold">#{{ $booking->id }}</p>
                </div>
                <div class="rounded-3xl bg-white/5 border border-white/10 px-4 py-3 grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.35em] text-slate-500">Date</p>
                        <p class="text-white font-semibold">{{ $booking->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.35em] text-slate-500">Card Number</p>
                        <p class="text-white font-semibold">{{ $booking->payment->masked_payment_method ?? 'Completed' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ url('/') }}" class="btn btn-secondary w-full">Go to homepage</a>
                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-primary w-full">View Tickets</a>
            </div>
        </div>
    </div>
</x-app-layout>