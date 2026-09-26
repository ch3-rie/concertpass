<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        $metrics = [
            'tickets_sold' => Ticket::count(),
            'revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
            'users' => User::count(),
            'concerts' => Concert::count(),
            'bookings' => Booking::count(),
        ];

        $recentTransactions = Booking::with(['user', 'concert'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('metrics', 'recentTransactions'));
    }

    public function analytics()
    {
        $monthlyRevenue = Payment::selectRaw("DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as month_key")
            ->selectRaw('SUM(amount) as total_amount')
            ->where('status', 'paid')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->limit(12)
            ->get();

        $monthlySalesCount = Payment::selectRaw("DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as month_key")
            ->selectRaw('COUNT(*) as payments_count')
            ->where('status', 'paid')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->limit(12)
            ->get();

        $monthlyUsers = User::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key")
            ->selectRaw('COUNT(*) as user_count')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->limit(12)
            ->get();

        $channelRevenue = Payment::where('status', 'paid')
            ->get()
            ->groupBy(fn (Payment $payment) => $payment->payment_method_label)
            ->map(function ($payments, $label) {
                return [
                    'payment_method' => $label,
                    'total_amount' => $payments->sum('amount'),
                ];
            })
            ->values();

        return view('admin.analytics', compact('monthlyRevenue', 'monthlySalesCount', 'monthlyUsers', 'channelRevenue'));
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.activity-logs', compact('logs'));
    }

    public function ticketManagement()
    {
        $ticketStats = Concert::query()
            ->with('venue')
            ->addSelect([
                'allocated_seats' => function ($query) {
                    $query->selectRaw('COALESCE(SUM(quantity), 0)')
                        ->from('concert_ticket_options')
                        ->whereColumn('concert_ticket_options.concert_id', 'concerts.id');
                },
                'sold_seats' => Ticket::selectRaw('COUNT(tickets.id)')
                    ->join('bookings', 'tickets.booking_id', '=', 'bookings.id')
                    ->whereColumn('bookings.concert_id', 'concerts.id'),
            ])
            ->orderByDesc('date')
            ->limit(12)
            ->get();

        return view('admin.ticket-management', compact('ticketStats'));
    }

}

