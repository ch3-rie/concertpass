<?php

namespace App\Services;

use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\Seat;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of truth for which venue seats are usable for a concert ticket type:
 * seeded seats define the full section pool; admin quantity N selects the first N seats
 * by numeric seat_number order. Availability is that subset minus sold seats.
 */
class ConcertSeatAvailabilityService
{
    /**
     * Types that use the seat picker (aligned with BookingController).
     *
     * @var list<string>
     */
    private const SEAT_PICKER_SLUGS = ['VIP Seated', 'LBB', 'UBB', 'LBA', 'UBA'];

    /**
     * @return list<string>
     */
    public function seatPickerSlugs(): array
    {
        return self::SEAT_PICKER_SLUGS;
    }

    public function requiresSeatSelection(?string $ticketTypeSlug): bool
    {
        return $ticketTypeSlug !== null
            && $ticketTypeSlug !== ''
            && in_array($ticketTypeSlug, self::SEAT_PICKER_SLUGS, true);
    }

    /**
     * Map ticket type slug (ticket_types.name) to venue seats.section value.
     */
    public function sectionForTicketTypeSlug(?string $ticketTypeSlug): ?string
    {
        if ($ticketTypeSlug === null || $ticketTypeSlug === '') {
            return null;
        }

        return match ($ticketTypeSlug) {
            'VIP Seated' => 'VIP Seated',
            'LBB' => 'Lower Box B (LBB)',
            'UBB' => 'Upper Box B (UBB)',
            'LBA' => 'Lower Box A (LBA)',
            'UBA' => 'Upper Box A (UBA)',
            'Gen Ad', 'GEN AD' => 'General Admission (Gen Ad)',
            default => null,
        };
    }

    public function remainingForTicketType(Concert $concert, ConcertTicketType $concertTicketType): int
    {
        $sold = Ticket::where('concert_ticket_type_id', $concertTicketType->id)->count();

        return max(0, (int) $concertTicketType->quantity - $sold);
    }


    public function seatsInSectionOrdered(int $venueId, string $section): Builder
    {
        return Seat::query()
            ->where('venue_id', $venueId)
            ->where('section', $section)
            ->where(function (Builder $q): void {
                $q->where('status', 'available')
                    ->orWhereNull('status');
            })
            ->orderByRaw('CAST(seat_number AS UNSIGNED) ASC');
    }

    /**
     * Seat IDs in the allowed subset for this ticket type (first N by ordering).
     *
     * @return list<int>
     */
    public function eligibleSeatIds(Concert $concert, ConcertTicketType $concertTicketType): array
    {
        $slug = $concertTicketType->ticketType->name ?? '';
        $section = $this->sectionForTicketTypeSlug($slug);
        if ($section === null) {
            return [];
        }

        $n = max(0, (int) $concertTicketType->quantity);
        if ($n === 0) {
            return [];
        }

        return $this->seatsInSectionOrdered((int) $concert->venue_id, $section)
            ->limit($n)
            ->pluck('id')
            ->all();
    }

    public function isSeatEligibleForTicketType(Seat $seat, Concert $concert, ConcertTicketType $concertTicketType): bool
    {
        $ids = $this->eligibleSeatIds($concert, $concertTicketType);

        return $ids !== [] && in_array((int) $seat->id, array_map('intval', $ids), true);
    }

    /**
     * Payload for seat dropdown API: only the controlled subset, with sold seats unavailable.
     *
     * @return list<array{id: int, seat_number: string, section: string, status: string}>
     */
    public function seatsPayloadForTicketType(Concert $concert, ConcertTicketType $concertTicketType): array
    {
        $slug = $concertTicketType->ticketType->name ?? '';
        $section = $this->sectionForTicketTypeSlug($slug);
        if ($section === null) {
            return [];
        }

        $ticketQuantity = max(0, (int) $concertTicketType->quantity);
        if ($ticketQuantity === 0) {
            return [];
        }

        $eligibleSeats = $this->seatsInSectionOrdered((int) $concert->venue_id, $section)
            ->limit($ticketQuantity)
            ->get();

        if ($eligibleSeats->isEmpty()) {
            return [];
        }

        $eligibleIds = $eligibleSeats->pluck('id')->all();

        $soldSeatIds = Ticket::query()
            ->where('concert_ticket_type_id', $concertTicketType->id)
            ->whereIn('seat_id', $eligibleIds)
            ->pluck('seat_id')
            ->all();

        return $eligibleSeats->map(function (Seat $seat) use ($soldSeatIds) {
            $status = in_array((int) $seat->id, array_map('intval', $soldSeatIds), true)
                ? 'unavailable'
                : 'available';

            return [
                'id' => (int) $seat->id,
                'seat_number' => (string) $seat->seat_number,
                'section' => (string) $seat->section,
                'status' => $status,
            ];
        })->values()->all();
    }
}
