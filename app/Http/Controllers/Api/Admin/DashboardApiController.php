<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function metrics()
    {
        return response()->json([
            'users' => User::count(),
            'concerts' => Concert::count(),
            'bookings' => Booking::count(),
            'tickets_sold' => Ticket::count(),
            'revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
        ]);
    }

    public function analytics()
    {
        $monthly = Payment::selectRaw("DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as month")
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('SUM(amount) as revenue')
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $channels = Payment::where('status', 'paid')
            ->get()
            ->groupBy(fn (Payment $payment) => $payment->payment_method_label)
            ->map(function ($payments, $label) {
                return [
                    'payment_method' => $label,
                    'total_amount' => (float) $payments->sum('amount'),
                ];
            })
            ->values();

        // Revenue per concert
        $concertRevenue = \App\Models\Concert::select(
            'concerts.id',
            'concerts.title',
            DB::raw('COALESCE(SUM(payments.amount),0) as total_revenue')
        )
            ->leftJoin('bookings', 'concerts.id', '=', 'bookings.concert_id')
            ->leftJoin('payments', function($join) {
                $join->on('bookings.id', '=', 'payments.booking_id')
                    ->where('payments.status', '=', 'paid');
            })
            ->groupBy('concerts.id', 'concerts.title')
            ->orderByDesc('total_revenue')
            ->get();

        $concertRevenue = $concertRevenue->map(function ($concert) {
            return [
                'id' => (int) $concert->id,
                'concert_name' => $concert->title,
                'total_revenue' => (float) $concert->total_revenue,
            ];
        })->values();

        return response()->json([
            'monthly' => $monthly,
            'channels' => $channels,
            'concerts' => $concertRevenue,
        ]);
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user:id,name,email')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($logs);
    }
}
