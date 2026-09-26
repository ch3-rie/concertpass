<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTransactionController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending,paid,failed'],
            'sort' => ['nullable', 'in:newest,oldest'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $sort = $validated['sort'] ?? 'newest';

        $transactions = Payment::query()
            ->with(['booking.user:id,name,email', 'booking.concert:id,title,date'])
            ->when(! empty($validated['search']), function ($query) use ($validated) {
                $search = trim($validated['search']);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('payments.id', $search)
                        ->orWhereHas('booking.user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when(! empty($validated['status']), function ($query) use ($validated) {
                $query->where('payments.status', $validated['status']);
            })
            ->when(! empty($validated['date_from']), function ($query) use ($validated) {
                $query->whereDate(DB::raw('COALESCE(payments.paid_at, payments.created_at)'), '>=', $validated['date_from']);
            })
            ->when(! empty($validated['date_to']), function ($query) use ($validated) {
                $query->whereDate(DB::raw('COALESCE(payments.paid_at, payments.created_at)'), '<=', $validated['date_to']);
            })
            ->orderByRaw('COALESCE(payments.paid_at, payments.created_at) '.($sort === 'oldest' ? 'asc' : 'desc'))
            ->paginate(12)
            ->withQueryString();

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'status' => $validated['status'] ?? '',
                'sort' => $sort,
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
        ]);
    }

    public function show(Payment $transaction)
    {
        $transaction->loadMissing([
            'booking.user:id,name,email',
            'booking.concert:id,title,date,time',
            'booking.tickets:id,booking_id,ticket_type,seat_id,price_at_purchase',
            'booking.tickets.seat:id,seat_number,section',
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }
}
