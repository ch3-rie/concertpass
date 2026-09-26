<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetConcertSeatsApiRequest;
use App\Http\Requests\Api\StoreConcertBookingApiRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Services\BookingService;
use App\Services\ConcertSeatAvailabilityService;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ConcertBookingApiController extends Controller
{
    public function __construct(
        private readonly ConcertSeatAvailabilityService $seatAvailability,
        private readonly BookingService $bookingService,
    ) {
    }

    public function ticketOptions(Concert $concert)
    {
        $concert->load(['venue', 'concertTicketTypes.ticketType']);

        $ticketOptions = $concert->concertTicketTypes->map(function (ConcertTicketType $ctt) use ($concert) {
            $slug = $ctt->ticketType->name ?? '';

            return [
                'id' => $ctt->id,
                'section' => $ctt->section,
                'price' => (string) $ctt->price,
                'color' => $ctt->color,
                'quantity_allocated' => (int) $ctt->quantity,
                'remaining' => $this->seatAvailability->remainingForTicketType($concert, $ctt),
                'requires_seat_selection' => $this->seatAvailability->requiresSeatSelection($slug),
                'ticket_type_name' => $slug,
            ];
        })->values()->all();

        return ApiResponse::success([
            'concert' => [
                'id' => $concert->id,
                'title' => $concert->title,
                'artist' => $concert->artist,
                'date' => $concert->date?->toDateString(),
                'time' => $concert->time ? $concert->time->format('H:i') : null,
                'venue' => $concert->venue ? [
                    'id' => $concert->venue->id,
                    'name' => $concert->venue->name,
                    'location' => $concert->venue->location,
                ] : null,
            ],
            'ticket_options' => $ticketOptions,
        ]);
    }

    public function seats(GetConcertSeatsApiRequest $request, Concert $concert)
    {
        $ticketTypeId = (int) $request->validated('concert_ticket_type_id');

        $concertTicketType = $concert->concertTicketTypes()->with('ticketType')->whereKey($ticketTypeId)->first();
        if (! $concertTicketType || (int) $concertTicketType->concert_id !== (int) $concert->id) {
            return ApiResponse::error('Invalid ticket option for this event.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $seats = $this->seatAvailability->seatsPayloadForTicketType($concert, $concertTicketType);

        return ApiResponse::success(['seats' => $seats]);
    }

    public function store(StoreConcertBookingApiRequest $request, Concert $concert)
    {
        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthorized.', Response::HTTP_UNAUTHORIZED);
        }

        $items = $request->decodedBookingItems();
        $paymentMethod = $request->input('card_number');

        try {
            $booking = $this->bookingService->confirmBooking($user, $concert, $items, $paymentMethod);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success([
            'booking_id' => $booking->id,
            'total_price' => (string) $booking->total_price,
            'tickets_url' => route('bookings.tickets', $booking),
        ], 'Booking confirmed.', Response::HTTP_CREATED);
    }
}
