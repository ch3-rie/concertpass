<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Redirect admins to admin dashboard
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $query = Concert::with('venue')->where('date', '>=', now()->toDateString());

        if ($request->filled('location')) {
            $query->whereHas('venue', function ($venueQuery) use ($request) {
                $venueQuery->where('location', $request->input('location'));
            });
        }

        $concerts = $query->orderBy('date')->get();

        $locations = Concert::with('venue')
            ->where('date', '>=', now()->toDateString())
            ->get()
            ->pluck('venue.location')
            ->unique()
            ->sort()
            ->values();

        $trending = Concert::with('venue')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->take(4)
            ->get();

        return view('home', compact('concerts', 'locations', 'trending'));
    }

    public function concerts(Request $request)
    {
        // Redirect admins to admin dashboard
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $query = Concert::with('venue')->where('date', '>=', now()->toDateString());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('artist', 'like', "%$search%");
            });
        }

        if ($request->filled('location')) {
            $query->whereHas('venue', function ($venueQuery) use ($request) {
                $venueQuery->where('location', $request->input('location'));
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        $concerts = $query->orderBy('date')->paginate(12);

        $locations = Concert::with('venue')
            ->where('date', '>=', now()->toDateString())
            ->get()
            ->pluck('venue.location')
            ->unique()
            ->sort()
            ->values();

        return view('concert.index', compact('concerts', 'locations'));
    }

    public function show(Concert $concert)
    {
        // Redirect admins to admin dashboard
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $concert->load(['venue', 'ticketPrices.ticketType']);
        $eventTicketTotal = $concert->totalTicketAllocation();

        return view('concert.show', compact('concert', 'eventTicketTotal'));
    }
}
