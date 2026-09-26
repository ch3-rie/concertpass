<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Persists bookings: locks seats, assigns Gen Ad using the same eligible pool as the seat API.
 */
class BookingService
{
    private const GEN_AD_SECTION = 'General Admission (Gen Ad)';

    public function __construct(
        private readonly ConcertSeatAvailabilityService $seatAvailability,
        private readonly BookingValidationService $bookingValidation,
        private readonly TicketInventoryService $ticketInventory,
    ) {
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function confirmBooking(User $user, Concert $concert, array $items, string $paymentMethod): Booking
    {
        $concert->loadMissing('concertTicketTypes.ticketType');
        $ticketTypes = $concert->concertTicketTypes->keyBy('id');
        $priceRecords = $ticketTypes->mapWithKeys(fn ($type) => [$type->id => $type->price])->all();

        [$seatItems, $autoAssignItems] = $this->partitionItems($items, $ticketTypes);

        $totalPrice = 0.0;
        foreach ($seatItems as $item) {
            $totalPrice += (float) $priceRecords[$item['concert_ticket_type_id']];
        }
        foreach ($autoAssignItems as $item) {
            $totalPrice += (float) $priceRecords[$item['concert_ticket_type_id']] * $item['quantity'];
        }

        return DB::transaction(function () use ($user, $concert, $seatItems, $autoAssignItems, $totalPrice, $priceRecords, $ticketTypes, $paymentMethod) {
            $totalQuantity = count($seatItems) + array_sum(array_column($autoAssignItems, 'quantity'));
            $demandByType = [];
            foreach ($seatItems as $item) {
                $typeId = (int) $item['concert_ticket_type_id'];
                $demandByType[$typeId] = ($demandByType[$typeId] ?? 0) + 1;
            }
            foreach ($autoAssignItems as $item) {
                $typeId = (int) $item['concert_ticket_type_id'];
                $demandByType[$typeId] = ($demandByType[$typeId] ?? 0) + (int) $item['quantity'];
            }

            $lockedTypes = ConcertTicketType::query()
                ->whereIn('id', array_keys($demandByType))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($demandByType as $typeId => $demand) {
                $concertTicketType = $lockedTypes->get($typeId);
                if (! $concertTicketType || (int) $concertTicketType->concert_id !== (int) $concert->id) {
                    throw new RuntimeException('Invalid ticket option for this event.');
                }

                $remaining = $this->ticketInventory->availableQuantityForType($concertTicketType);
                if ((int) $demand > $remaining) {
                    throw new RuntimeException('Not enough tickets remain for one of your selected types.');
                }
            }

            $sortedSeatIds = collect($seatItems)
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            if ($sortedSeatIds !== []) {
                Seat::query()->whereIn('id', $sortedSeatIds)->lockForUpdate()->get();
            }

            foreach ($seatItems as $item) {
                $concertTicketType = $ticketTypes[$item['concert_ticket_type_id']];
                $seatId = (int) $item['seat_id'];

                $stillAvail = $this->bookingValidation->assertSeatStillAvailable($concert, $seatId);
                if ($stillAvail !== null) {
                    throw new RuntimeException($stillAvail);
                }

                $seat = Seat::query()->whereKey($seatId)->lockForUpdate()->first();
                if (! $seat || (int) $seat->venue_id !== (int) $concert->venue_id
                    || ! $this->seatAvailability->isSeatEligibleForTicketType($seat, $concert, $concertTicketType)) {
                    throw new RuntimeException('Invalid seat selection.');
                }
            }

            $booking = Booking::create([
                'user_id' => $user->id,
                'concert_id' => $concert->id,
                'total_price' => $totalPrice,
                'status' => 'confirmed',
            ]);

            foreach ($seatItems as $item) {
                $concertTicketType = $ticketTypes[$item['concert_ticket_type_id']];
                $ticketTypeSlug = $concertTicketType->ticketType->name ?? '';
                $ticketTypeLabel = $concertTicketType->custom_name ?: ($concertTicketType->ticketType->description ? $concertTicketType->ticketType->description.' ('.$ticketTypeSlug.')' : $ticketTypeSlug);

                try {
                    Ticket::create([
                        'booking_id' => $booking->id,
                        'concert_ticket_type_id' => $item['concert_ticket_type_id'],
                        'seat_id' => $item['seat_id'],
                        'ticket_type' => $ticketTypeLabel,
                        'price_at_purchase' => $priceRecords[$item['concert_ticket_type_id']],
                        'qr_code' => uniqid(),
                    ]);
                } catch (QueryException $e) {
                    if ($this->isUniqueViolation($e)) {
                        throw new RuntimeException('Seat already taken. Please choose another seat.');
                    }
                    throw $e;
                }
            }

            foreach ($autoAssignItems as $item) {
                $concertTicketType = $ticketTypes[$item['concert_ticket_type_id']];
                $ticketTypeSlug = $concertTicketType->ticketType->name ?? '';
                $ticketTypeLabel = $concertTicketType->custom_name ?: ($concertTicketType->ticketType->description ? $concertTicketType->ticketType->description.' ('.$ticketTypeSlug.')' : $ticketTypeSlug);
                $assignedSeatIds = $this->allocateGeneralAdmissionSeatIds($concert, $concertTicketType, (int) $item['quantity']);

                foreach ($assignedSeatIds as $assignedSeatId) {
                    if ($assignedSeatId === null) {
                        continue;
                    }
                    $seatFreeMsg = $this->bookingValidation->assertSeatStillAvailable($concert, (int) $assignedSeatId);
                    if ($seatFreeMsg !== null) {
                        throw new RuntimeException($seatFreeMsg);
                    }
                }

                for ($i = 0; $i < $item['quantity']; $i++) {
                    try {
                        Ticket::create([
                            'booking_id' => $booking->id,
                            'concert_ticket_type_id' => $item['concert_ticket_type_id'],
                            'seat_id' => $assignedSeatIds[$i] ?? null,
                            'ticket_type' => $ticketTypeLabel,
                            'price_at_purchase' => $priceRecords[$item['concert_ticket_type_id']],
                            'qr_code' => uniqid(),
                        ]);
                    } catch (QueryException $e) {
                        if ($this->isUniqueViolation($e)) {
                            throw new RuntimeException('Not enough seats available. Please try again with a lower quantity.');
                        }
                        throw $e;
                    }
                }
            }

            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $totalPrice,
                'payment_method' => $paymentMethod,
                'status' => 'paid',
            ]);

            ActivityLog::record([
                'user_id' => $user->id,
                'action' => 'create',
                'entity_type' => 'booking',
                'entity_id' => $booking->id,
                'description' => 'Booked tickets for concert: '.$concert->title.' ('.$totalQuantity.' tickets, ₱'.number_format($totalPrice, 2).')',
            ]);

            return $booking;
        });
    }

    /**
     * Totals for review/checkout views.
     *
     * @param  list<array<string, mixed>>  $items
     * @return array{totalQuantity: int, totalPrice: float, priceRecords: array<int, float|int|string>}
     */
    public function calculateTotals(Concert $concert, array $items): array
    {
        $concert->loadMissing('concertTicketTypes.ticketType');
        $ticketTypes = $concert->concertTicketTypes->keyBy('id');
        $priceRecords = $ticketTypes->mapWithKeys(fn ($type) => [$type->id => $type->price])->all();

        $totalQuantity = 0;
        $totalPrice = 0.0;

        foreach ($items as $item) {
            $ticketTypeId = (int) ($item['concert_ticket_type_id'] ?? 0);
            if (! isset($ticketTypes[$ticketTypeId])) {
                continue;
            }
            $slug = $ticketTypes[$ticketTypeId]->ticketType->name ?? '';
            $quantity = $this->seatAvailability->requiresSeatSelection($slug)
                ? 1
                : max(1, (int) ($item['quantity'] ?? 1));
            $totalQuantity += $quantity;
            $totalPrice += (float) $priceRecords[$ticketTypeId] * $quantity;
        }

        return [
            'totalQuantity' => $totalQuantity,
            'totalPrice' => $totalPrice,
            'priceRecords' => $priceRecords,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ConcertTicketType>  $ticketTypes
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    public function partitionItems(array $items, Collection $ticketTypes): array
    {
        $seatItems = [];
        $autoAssignItems = [];

        foreach ($items as $item) {
            $ticketTypeId = (int) ($item['concert_ticket_type_id'] ?? 0);
            $concertTicketType = $ticketTypes[$ticketTypeId];
            $slug = $concertTicketType->ticketType->name ?? '';

            if ($this->seatAvailability->requiresSeatSelection($slug)) {
                $seatItems[] = [
                    'concert_ticket_type_id' => $ticketTypeId,
                    'seat_id' => (int) $item['seat_id'],
                ];
            } else {
                $autoAssignItems[] = [
                    'concert_ticket_type_id' => $ticketTypeId,
                    'quantity' => (int) $item['quantity'],
                ];
            }
        }

        return [$seatItems, $autoAssignItems];
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        return $e->getCode() === '23000'
            || str_contains((string) $e->getMessage(), 'Duplicate entry')
            || str_contains((string) $e->getMessage(), 'UNIQUE constraint');
    }

    /**
     * First available seats in numeric order within the admin-limited eligible pool (matches seat dropdown).
     *
     * @return list<int|null>
     */
    private function allocateGeneralAdmissionSeatIds(Concert $concert, ConcertTicketType $concertTicketType, int $quantity): array
    {
        $slug = $concertTicketType->ticketType->name ?? '';
        $section = $this->seatAvailability->sectionForTicketTypeSlug($slug);
        if ($section !== self::GEN_AD_SECTION) {
            return array_fill(0, $quantity, null);
        }

        if ($quantity < 1) {
            throw new RuntimeException('Invalid ticket quantity.');
        }

        $ticketLimit = max(0, (int) $concertTicketType->quantity);
        if ($ticketLimit < $quantity) {
            throw new RuntimeException('Not enough General Admission tickets are available.');
        }

        $eligibleIds = $this->seatAvailability->eligibleSeatIds($concert, $concertTicketType);
        if ($eligibleIds === []) {
            throw new RuntimeException('Not enough General Admission seats are currently available.');
        }

        $eligibleSeats = Seat::query()
            ->whereIn('id', $eligibleIds)
            ->orderByRaw('CAST(seat_number AS UNSIGNED) ASC')
            ->lockForUpdate()
            ->get(['id', 'seat_number']);

        $soldSeatIds = Ticket::query()
            ->where('concert_ticket_type_id', $concertTicketType->id)
            ->whereIn('seat_id', $eligibleIds)
            ->lockForUpdate()
            ->pluck('seat_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $available = $eligibleSeats
            ->reject(fn ($seat) => in_array((int) $seat->id, $soldSeatIds, true))
            ->values();

        if ($available->count() < $quantity) {
            throw new RuntimeException('Not enough General Admission seats are currently available.');
        }

        return $available->take($quantity)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
