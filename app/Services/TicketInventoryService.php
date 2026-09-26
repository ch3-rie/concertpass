<?php

namespace App\Services;

use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\Ticket;
use App\Models\Venue;
use Illuminate\Support\Collection;

class TicketInventoryService
{
    public function soldQuantityForType(ConcertTicketType $concertTicketType): int
    {
        return (int) Ticket::query()
            ->where('concert_ticket_type_id', $concertTicketType->id)
            ->count();
    }

    public function availableQuantityForType(ConcertTicketType $concertTicketType): int
    {
        return max(0, (int) $concertTicketType->quantity - $this->soldQuantityForType($concertTicketType));
    }

    /**
     * @param  Collection<int, ConcertTicketType>|null  $types
     */
    public function soldQuantityForConcert(Concert $concert, ?Collection $types = null): int
    {
        $types = $types ?? $concert->concertTicketTypes()->get();

        if ($types->isEmpty()) {
            return 0;
        }

        return (int) Ticket::query()
            ->whereIn('concert_ticket_type_id', $types->pluck('id')->all())
            ->count();
    }

    /**
     * @param  list<array<string, mixed>>  $ticketTypesPayload
     */
    public function validateTotalAgainstVenueCapacity(array $ticketTypesPayload, Venue $venue): ?string
    {
        $totalQuantity = collect($ticketTypesPayload)->sum(fn ($row) => (int) ($row['quantity'] ?? 0));
        if ($totalQuantity > (int) $venue->capacity) {
            return sprintf(
                'The total ticket quantity cannot exceed the selected venue capacity (%d). Current total: %d.',
                $venue->capacity,
                $totalQuantity
            );
        }

        return null;
    }
}
