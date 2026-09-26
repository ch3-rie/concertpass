<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueApiController extends Controller
{

    public function index()
    {
        return response()->json(Venue::orderByDesc('id')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
        ]);

        $venue = Venue::create($data);
        return response()->json($venue, 201);
    }

    public function show(Venue $venue)
    {
        return response()->json($venue->load('seats'));
    }

    public function update(Request $request, Venue $venue)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
        ]);

        $oldCapacity = $venue->capacity;
        $newCapacity = $data['capacity'];

        // Check ticket allocations for existing concerts
        if ($newCapacity != $oldCapacity) {
            $concerts = $venue->concerts()->with('concertTicketTypes.ticketType')->get();

            foreach ($concerts as $concert) {
                $totalSold = Ticket::whereHas('concertTicketType', function($q) use ($concert) {
                    $q->where('concert_id', $concert->id);
                })->count();

                if ($newCapacity < $totalSold) {
                    return response()->json(['error' => "Cannot reduce capacity below already sold tickets ({$totalSold}) for concert '{$concert->title}'."], 422);
                }
            }
        }

        $venue->update($data);
        return response()->json($venue);
    }

    public function destroy(Venue $venue)
    {
        $venue->delete();
        return response()->json(['message' => 'Venue deleted']);
    }
}
