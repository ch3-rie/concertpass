<?php

namespace App\Services;

use App\Models\Concert;
use App\Models\Seat;
use App\Models\Ticket;

/**
 * Booking line-item validation: limits, per-type availability,
 * seat eligibility, and duplicate-seat prevention (single source of truth with seat API).
 */
class BookingValidationService
{
    /** Aligned with booking UI copy ("Max 5 tickets per transaction"). */
    public const MAX_TICKETS_PER_TRANSACTION = 5;

    public function __construct(
        private readonly ConcertSeatAvailabilityService $seatAvailability,
    ) {
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function validateBookingItems(Concert $concert, array $items): ?string
    {
        if ($items === []) {
            return 'Please select at least one ticket.';
        }

        if (! $this->isIndexedList($items)) {
            return 'Invalid selection.';
        }

        $concert->loadMissing('concertTicketTypes.ticketType', 'venue');
        $ticketTypes = $concert->concertTicketTypes->keyBy('id');

        $seatIdsInCart = [];
        $demandByType = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                return 'Invalid selection.';
            }

            $typeId = isset($item['concert_ticket_type_id']) ? (int) $item['concert_ticket_type_id'] : 0;
            if ($typeId < 1 || ! $ticketTypes->has($typeId)) {
                return 'Invalid ticket option.';
            }

            $concertTicketType = $ticketTypes[$typeId];
            if ((int) $concertTicketType->concert_id !== (int) $concert->id) {
                return 'Invalid ticket option for this event.';
            }

            $slug = $concertTicketType->ticketType->name ?? '';

            if ($this->seatAvailability->requiresSeatSelection($slug)) {
                $seatId = isset($item['seat_id']) ? (int) $item['seat_id'] : 0;
                if ($seatId < 1) {
                    return 'Please select a seat for each seated ticket.';
                }

                if (in_array($seatId, $seatIdsInCart, true)) {
                    return 'Each seat can only be selected once.';
                }
                $seatIdsInCart[] = $seatId;
                $demandByType[$typeId] = ($demandByType[$typeId] ?? 0) + 1;
            } else {
                $qty = isset($item['quantity']) ? (int) $item['quantity'] : 0;
                if ($qty < 1) {
                    return 'Ticket quantity must be a positive number.';
                }
                $demandByType[$typeId] = ($demandByType[$typeId] ?? 0) + $qty;
            }
        }

        $totalTickets = array_sum($demandByType);
        if ($totalTickets < 1) {
            return 'Please select at least one ticket.';
        }

        if ($totalTickets > self::MAX_TICKETS_PER_TRANSACTION) {
            return 'You can purchase at most '.self::MAX_TICKETS_PER_TRANSACTION.' tickets per transaction.';
        }

        $eventAllocation = $concert->totalTicketAllocation();
        $soldTotal = (int) Ticket::query()
            ->whereHas('booking', fn ($q) => $q->where('concert_id', $concert->id))
            ->count();

        if ($soldTotal + $totalTickets > $eventAllocation) {
            return 'Not enough tickets remain for this event.';
        }

        foreach ($demandByType as $typeId => $demand) {
            $concertTicketType = $ticketTypes[$typeId];
            $remaining = $this->seatAvailability->remainingForTicketType($concert, $concertTicketType);
            if ($demand > $remaining) {
                return 'Not enough tickets remain for one of your selected types.';
            }
        }

        foreach ($items as $item) {
            $typeId = (int) ($item['concert_ticket_type_id'] ?? 0);
            $concertTicketType = $ticketTypes[$typeId];
            $slug = $concertTicketType->ticketType->name ?? '';

            if (! $this->seatAvailability->requiresSeatSelection($slug)) {
                continue;
            }

            $seatId = (int) $item['seat_id'];
            $seat = Seat::query()->find($seatId);
            if (! $seat) {
                return 'Invalid selection.';
            }
            if ((int) $seat->venue_id !== (int) $concert->venue_id) {
                return 'Invalid selection.';
            }
            if (! $this->seatAvailability->isSeatEligibleForTicketType($seat, $concert, $concertTicketType)) {
                return 'Invalid selection.';
            }
            if ($this->isSeatTakenForConcert($concert, $seatId)) {
                return 'Seat already taken. Please choose another seat.';
            }
        }

        return null;
    }

    /**
     * Final check inside a transaction after row locks (prevents double booking).
     */
    public function assertSeatStillAvailable(Concert $concert, int $seatId): ?string
    {
        if ($this->isSeatTakenForConcert($concert, $seatId)) {
            return 'Seat already taken. Please choose another seat.';
        }

        return null;
    }

    private function isSeatTakenForConcert(Concert $concert, int $seatId): bool
    {
        return Ticket::query()
            ->where('seat_id', $seatId)
            ->whereHas('booking', fn ($q) => $q->where('concert_id', $concert->id))
            ->exists();
    }

    private function isIndexedList(array $value): bool
    {
        $i = 0;

        foreach ($value as $k => $_) {
            if ($k !== $i) {
                return false;
            }
            $i++;
        }

        return true;
    }
}
