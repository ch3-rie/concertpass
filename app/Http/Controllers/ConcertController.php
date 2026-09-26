<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Venue;
use App\Services\ConcertSeatAvailabilityService;
use App\Services\TicketInventoryService;
use App\Services\VenueSeatPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConcertController extends Controller
{
    public function __construct(
        private readonly VenueSeatPoolService $venueSeatPool,
        private readonly ConcertSeatAvailabilityService $seatAvailability,
        private readonly TicketInventoryService $ticketInventory,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $concerts = Concert::with('venue')->paginate(10);
        $soldConcertIds = Ticket::query()
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->distinct()
            ->pluck('bookings.concert_id');

        return view('admin.concerts.index', compact('concerts', 'soldConcertIds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $venues = Venue::all();
        $ticketTypes = TicketType::all();

        if ($ticketTypes->isEmpty()) {
            $defaultTypes = [
                ['name' => 'VIP Standing', 'description' => 'VIP Standing'],
                ['name' => 'VIP Seated', 'description' => 'VIP Seated'],
                ['name' => 'LBB', 'description' => 'Lower Box B'],
                ['name' => 'UBB', 'description' => 'Upper Box B'],
                ['name' => 'LBA', 'description' => 'Lower Box A'],
                ['name' => 'UBA', 'description' => 'Upper Box A'],
                ['name' => 'GEN AD', 'description' => 'General Admission'],
            ];

            foreach ($defaultTypes as $type) {
                TicketType::firstOrCreate(['name' => $type['name']], ['description' => $type['description']]);
            }

            $ticketTypes = TicketType::all();
        }

        return view('admin.concerts.create', compact('venues', 'ticketTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('concerts', 'title')],
            'description' => 'nullable|string|max:20000',
            'artist' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'date' => 'required|date|after:today',
            'time' => 'required|date_format:H:i',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ticket_types' => 'required|array|min:1',
            'ticket_types.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'ticket_types.*.price' => 'required|numeric|min:0',
            'ticket_types.*.quantity' => 'required|integer|min:1',
            'ticket_types.*.color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ], [
            'title.unique' => 'This concert already exists.',
        ]);

        $data = $request->only(['title', 'description', 'artist', 'venue_id', 'date', 'time']);

        if ($request->hasFile('poster')) {
            $data['poster_url'] = $request->file('poster')->store('posters', 'public');
        }

        $data['seat_plan_image'] = $request->hasFile('seat_plan_image') ? $request->file('seat_plan_image')->store('seat-plans', 'public') : null;

        $venue = Venue::findOrFail((int) $request->venue_id);
        $capacityError = $this->ticketInventory->validateTotalAgainstVenueCapacity($request->ticket_types, $venue);
        if ($capacityError !== null) {
            return back()
                ->withInput()
                ->withErrors(['ticket_types' => $capacityError]);
        }

        $concert = Concert::create($data);
        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'create',
            'entity_type' => 'concert',
            'entity_id' => $concert->id,
            'description' => 'Created concert: '.$concert->title,
        ]);

        // Create concert ticket types
        foreach ($request->ticket_types as $ticketData) {
            ConcertTicketType::create([
                'concert_id' => $concert->id,
                'ticket_type_id' => $ticketData['ticket_type_id'],
                'custom_name' => $ticketData['custom_name'] ?? null,
                'price' => $ticketData['price'],
                'quantity' => $ticketData['quantity'],
                'color' => $ticketData['color'],
            ]);
        }

        $venueModel = Venue::findOrFail($concert->venue_id);
        $this->venueSeatPool->syncPhysicalSeatsForVenueTicketTypes(
            $venueModel,
            $concert->concertTicketTypes()->with('ticketType')->get()
        );

        return redirect()->route('admin.concerts.index')->with('success', 'Concert created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Concert $concert)
    {
        $concert->load(['venue', 'concertTicketTypes.ticketType', 'bookings']);

        $ticketTypeSalesById = Ticket::whereHas('booking', function ($query) use ($concert) {
            $query->where('concert_id', $concert->id);
        })
        ->whereNotNull('concert_ticket_type_id')
        ->select('concert_ticket_type_id', DB::raw('count(*) as count'))
        ->groupBy('concert_ticket_type_id')
        ->pluck('count', 'concert_ticket_type_id')
        ->all();

        $ticketTypeAvailability = $concert->concertTicketTypes->map(function (ConcertTicketType $concertTicketType) use ($concert, $ticketTypeSalesById) {
            $ticketTypeSlug = $concertTicketType->ticketType->name ?? '';
            $ticketTypeLabel = $concertTicketType->custom_name ?: ($concertTicketType->ticketType->description ? $concertTicketType->ticketType->description . ' (' . $ticketTypeSlug . ')' : $ticketTypeSlug);
            $sold = $ticketTypeSalesById[$concertTicketType->id] ?? 0;
            $remaining = $this->seatAvailability->remainingForTicketType($concert, $concertTicketType);

            return [
                'label' => $ticketTypeLabel,
                'slug' => $ticketTypeSlug,
                'quantity' => $concertTicketType->quantity,
                'sold' => $sold,
                'remaining' => $remaining,
            ];
        });

        return view('admin.concerts.show', compact('concert', 'ticketTypeAvailability'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Concert $concert)
    {
        $venues = Venue::all();
        $ticketTypes = TicketType::all();
        $concert->load('concertTicketTypes.ticketType');
        $hasSoldTickets = Ticket::whereHas('booking', function ($query) use ($concert) {
            $query->where('concert_id', $concert->id);
        })->exists();

        return view('admin.concerts.edit', compact('concert', 'venues', 'ticketTypes', 'hasSoldTickets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Concert $concert)
    {
        $hasSoldTickets = Ticket::whereHas('booking', function ($query) use ($concert) {
            $query->where('concert_id', $concert->id);
        })->exists();

        $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('concerts', 'title')->ignore($concert->id)],
            'description' => 'nullable|string|max:20000',
            'artist' => 'required|string|max:255',
            'venue_id' => ['required', 'exists:venues,id', function ($attribute, $value, $fail) use ($concert) {
                if ($value != $concert->venue_id) {
                    $fail('Venue cannot be changed after concert creation.');
                }
            }],
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'seat_plan_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ticket_types' => 'required|array|min:1',
            'ticket_types.*.id' => 'nullable|exists:concert_ticket_options,id',
            'ticket_types.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'ticket_types.*.custom_name' => 'nullable|string|max:255',
            'ticket_types.*.price' => 'required|numeric|min:0',
            'ticket_types.*.quantity' => 'required|integer|min:1',
            'ticket_types.*.color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ], [
            'title.unique' => 'This concert already exists.',
        ]);

        $existingTypes = $concert->concertTicketTypes()->get()->keyBy('id');

        $data = $request->only(['title', 'description', 'artist', 'venue_id', 'date', 'time']);

        if ($request->hasFile('poster')) {
            $data['poster_url'] = $request->file('poster')->store('posters', 'public');
        }
        
        if ($request->hasFile('seat_plan_image')) {
            $data['seat_plan_image'] = $request->file('seat_plan_image')->store('seat-plans', 'public');
        }

        $venue = Venue::findOrFail((int) $request->venue_id);
        $capacityError = $this->ticketInventory->validateTotalAgainstVenueCapacity($request->ticket_types, $venue);
        if ($capacityError !== null) {
            return back()
                ->withInput()
                ->withErrors(['ticket_types' => $capacityError]);
        }

        $oldVenueId = $concert->venue_id;
        $concert->update($data);
        $venueChanged = $oldVenueId != $request->venue_id;

        if ($venueChanged && $hasSoldTickets) {
            return back()
                ->withInput()
                ->withErrors(['venue_id' => 'Venue cannot be changed once tickets are already sold for this concert.']);
        }

        foreach ($request->ticket_types as $ticketData) {
            if (!empty($ticketData['id']) && $existingTypes->has($ticketData['id'])) {
                $concertTicketType = $existingTypes->get($ticketData['id']);
                $soldCount = $this->ticketInventory->soldQuantityForType($concertTicketType);

                if ($ticketData['quantity'] < $soldCount) {
                    return back()
                        ->withInput()
                        ->withErrors(['ticket_types' => "Ticket quantity for {$concertTicketType->ticketType->name} cannot be less than already sold tickets ({$soldCount})."]);
                }

                $priceOrColorChanged = (string) $concertTicketType->price !== (string) $ticketData['price']
                    || (string) $concertTicketType->color !== (string) $ticketData['color'];

                if ($hasSoldTickets && $priceOrColorChanged) {
                    return back()
                        ->withInput()
                        ->withErrors(['ticket_types' => 'Ticket pricing and colors are locked because tickets have already been sold for this concert.']);
                }

                $concertTicketType->update([
                    'ticket_type_id' => (int) $ticketData['ticket_type_id'],
                    'custom_name' => $ticketData['custom_name'] ?? null,
                    'price' => $ticketData['price'],
                    'quantity' => $ticketData['quantity'],
                    'color' => $ticketData['color'],
                ]);
            } else {
                return back()
                    ->withInput()
                    ->withErrors(['ticket_types' => 'You can only edit existing ticket types in this form.']);
            }
        }

        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'update',
            'entity_type' => 'concert',
            'entity_id' => $concert->id,
            'description' => 'Updated concert: '.$concert->title,
        ]);

        $venueModel = Venue::findOrFail($concert->venue_id);
        $this->venueSeatPool->syncPhysicalSeatsForVenueTicketTypes(
            $venueModel,
            $concert->concertTicketTypes()->with('ticketType')->get()
        );

        $successMsg = 'Concert updated successfully.';
        if ($venueChanged) {
            $successMsg .= ' Venue changed.';
        }
        return redirect()->route('admin.concerts.index')->with('success', $successMsg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Concert $concert)
    {
        if ($concert->hasSoldTickets()) {
            return redirect()->route('admin.concerts.index')->with(
                'error',
                'This concert cannot be deleted because tickets have already been sold.'
            );
        }

        $title = $concert->title;
        $id = $concert->id;
        $concert->delete();
        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'entity_type' => 'concert',
            'entity_id' => $id,
            'description' => 'Deleted concert: '.$title,
        ]);
        return redirect()->route('admin.concerts.index')->with('success', 'Concert deleted successfully.');
    }
}

