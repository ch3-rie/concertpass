<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Concert;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{

    public function index()
    {
        $venues = Venue::paginate(10);
        return view('admin.venues.index', compact('venues'));
    }

    public function create()
    {
        return view('admin.venues.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'seat_plan_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'location', 'capacity']);

        if ($request->hasFile('seat_plan_image')) {
            $data['seat_plan_image'] = $request->file('seat_plan_image')->store('venue-plans', 'public');
        }

        $venue = Venue::create($data);

        $this->createVenueSeats($venue, $data['capacity']);

        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'create',
            'entity_type' => 'venue',
            'entity_id' => $venue->id,
            'description' => 'Created venue: '.$venue->name,
        ]);

        return redirect()->route('admin.venues.index')->with('success', 'Venue created successfully.');
    }


    public function show(Venue $venue)
    {
        return view('admin.venues.show', compact('venue'));
    }

    public function edit(Venue $venue)
    {
        $isUsedByConcerts = $venue->concerts()->exists();
        $concertSoldBreakdown = $venue->concerts()
            ->withCount([
                'bookings as sold_tickets_count' => function ($query) {
                    $query->join('tickets', 'tickets.booking_id', '=', 'bookings.id');
                },
            ])
            ->get(['id', 'title'])
            ->map(fn ($concert) => [
                'title' => $concert->title,
                'sold' => (int) $concert->sold_tickets_count,
            ]);

        $maxSoldForConcert = (int) ($concertSoldBreakdown->max('sold') ?? 0);

        return view('admin.venues.edit', compact('venue', 'isUsedByConcerts', 'maxSoldForConcert', 'concertSoldBreakdown'));
    }

    public function update(Request $request, Venue $venue)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'seat_plan_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'location', 'capacity']);

        if ($request->hasFile('seat_plan_image')) {
            $data['seat_plan_image'] = $request->file('seat_plan_image')->store('venue-plans', 'public');
        }

        $oldCapacity = $venue->capacity;
        $newCapacity = $data['capacity'];
        $isUsedByConcerts = $venue->concerts()->exists();

        if ($isUsedByConcerts) {
            $nameChanged = (string) $data['name'] !== (string) $venue->name;
            $locationChanged = (string) $data['location'] !== (string) $venue->location;
            if ($nameChanged || $locationChanged) {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'Venue name/location cannot be edited once the venue is already used by concerts. Only capacity increase is allowed.']);
            }

        }

        $concerts = $venue->concerts()->with('concertTicketTypes.ticketType')->get();

        // Check ticket allocations for existing concerts
        if ($newCapacity != $oldCapacity) {
            foreach ($concerts as $concert) {
                $totalSold = Ticket::whereHas('concertTicketType', function($q) use ($concert) {
                    $q->where('concert_id', $concert->id);
                })->count();

                if ($newCapacity < $totalSold) {
                    return back()
                        ->withInput()
                        ->withErrors(['capacity' => "Cannot reduce capacity below already sold tickets ({$totalSold}) for concert '{$concert->title}'."]);
                }
            }
        }

        $venue->update($data);

        // For venues already used by concerts, keep existing seat rows stable so
        // seat-linked availability and sold-seat references do not shift unexpectedly.
        if (! $isUsedByConcerts && ($newCapacity != $oldCapacity || $venue->seats()->count() === 0)) {
            $this->createVenueSeats($venue, $newCapacity);
        }

        $description = 'Updated venue: '.$venue->name;
        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'update',
            'entity_type' => 'venue',
            'entity_id' => $venue->id,
            'description' => $description,
        ]);

        return redirect()->route('admin.venues.index')->with('success', 'Venue updated successfully.');
    }

    public function destroy(Venue $venue)
    {
        // Check if venue is referenced by any concerts
        $hasConcerts = $venue->concerts()->exists();

        if ($hasConcerts) {
            return back()->with('error', 'Cannot delete this venue because it is currently used by one or more concerts.');
        }

        $name = $venue->name;
        $id = $venue->id;
        $venue->delete();
        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'entity_type' => 'venue',
            'entity_id' => $id,
            'description' => 'Deleted venue: '.$name,
        ]);
        return redirect()->route('admin.venues.index')->with('success', 'Venue deleted successfully.');
    }

    private function createVenueSeats(Venue $venue, int $capacity): void
    {
        Seat::where('venue_id', $venue->id)->delete();

        $baseSections = [
            'VIP Seated' => 20,
            'Lower Box B (LBB)' => 50,
            'Upper Box B (UBB)' => 100,
            'Lower Box A (LBA)' => 50,
            'Upper Box A (UBA)' => 50,
            'General Admission (Gen Ad)' => 30,
        ];

        $totalBaseSeats = array_sum($baseSections);
        $scale = $capacity / $totalBaseSeats;

        $sections = [];
        foreach ($baseSections as $section => $count) {
            $sections[$section] = range(1, (int) round($count * $scale));
        }

        $totalSeats = 0;
        foreach ($sections as $section => $seats) {
            $totalSeats += count($seats);
            foreach ($seats as $seatNumber) {
                Seat::create([
                    'venue_id' => $venue->id,
                    'seat_number' => (string) $seatNumber,
                    'section' => $section,
                ]);
            }
        }

        \Log::info('Created ' . $totalSeats . ' seats for venue ' . $venue->id);
    }
}


