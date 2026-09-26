<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutBookingRequest;
use App\Http\Requests\ConfirmBookingPaymentRequest;
use App\Http\Requests\GetBookingSeatsRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Services\BookingService;
use App\Services\BookingValidationService;
use App\Services\ConcertSeatAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class BookingController extends Controller
{
    public function __construct(
        private readonly ConcertSeatAvailabilityService $seatAvailability,
        private readonly BookingValidationService $bookingValidation,
        private readonly BookingService $bookingService,
    ) {
    }

    public function index()
    {
        $bookings = Auth::user()->bookings()
            ->with('concert.venue', 'tickets')
            ->orderBy('id', 'desc')
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create(Concert $concert)
    {
        $concert->load(['venue', 'concertTicketTypes.ticketType', 'bookings.tickets']);
        $totalSold = $concert->bookings->sum(fn ($b) => $b->tickets->count());
        $eventTicketTotal = $concert->totalTicketAllocation();
        $remainingEventTickets = max(0, $eventTicketTotal - $totalSold);

        $ticketAvailability = $concert->concertTicketTypes->mapWithKeys(function (ConcertTicketType $ctt) use ($concert) {
            return [$ctt->id => $this->seatAvailability->remainingForTicketType($concert, $ctt)];
        })->all();

        return view('bookings.create', compact('concert', 'remainingEventTickets', 'eventTicketTotal', 'ticketAvailability'));
    }

    public function getSeats(GetBookingSeatsRequest $request, Concert $concert)
    {
        $ticketTypeId = (int) $request->validated('concert_ticket_type_id');

        $concertTicketType = $concert->concertTicketTypes()->with('ticketType')->whereKey($ticketTypeId)->first();
        if (! $concertTicketType || (int) $concertTicketType->concert_id !== (int) $concert->id) {
            return ApiResponse::error('Invalid ticket option for this event.', 422);
        }

        return ApiResponse::success([
            'seats' => $this->seatAvailability->seatsPayloadForTicketType($concert, $concertTicketType),
        ]);
    }

    public function store(StoreBookingRequest $request, Concert $concert)
    {
        $items = $request->decodedBookingItems();

        return redirect()
            ->route('bookings.review', $concert)
            ->with('booking_payload', json_encode($items));
    }

    public function review(Request $request, Concert $concert)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'booking_items' => ['required', 'string', 'json'],
            ]);
            $items = json_decode((string) $request->input('booking_items'), true);
            if (! is_array($items) || ! array_is_list($items)) {
                return back()->withErrors(['booking_items' => 'Invalid booking data.']);
            }
            $validationError = $this->bookingValidation->validateBookingItems($concert, $items);
            if ($validationError !== null) {
                return back()->withErrors(['booking_items' => $validationError]);
            }

            return redirect()
                ->route('bookings.review', $concert)
                ->with('booking_payload', (string) $request->input('booking_items'));
        }

        $raw = session('booking_payload');
        if (! is_string($raw)) {
            return redirect()
                ->route('bookings.create', $concert)
                ->withErrors(['general' => 'Session expired. Please select tickets again.']);
        }

        $cartItems = json_decode($raw, true);
        if (! is_array($cartItems) || ! array_is_list($cartItems)) {
            return redirect()
                ->route('bookings.create', $concert)
                ->withErrors(['general' => 'Invalid booking data.']);
        }

        $validationError = $this->bookingValidation->validateBookingItems($concert, $cartItems);
        if ($validationError !== null) {
            return redirect()
                ->route('bookings.create', $concert)
                ->withErrors(['booking_items' => $validationError]);
        }

        $cartTotals = $this->bookingService->calculateTotals($concert, $cartItems);
        $totalQuantity = $cartTotals['totalQuantity'];
        $totalPrice = $cartTotals['totalPrice'];
        $priceRecords = $cartTotals['priceRecords'];

        $selectedSeats = [];
        foreach ($cartItems as $item) {
            if (isset($item['seat_id'])) {
                $selectedSeats[] = $item;
            }
        }

        $concert->load('venue', 'concertTicketTypes');
        $eventTicketTotal = $concert->totalTicketAllocation();
        $bookingPayloadJson = $raw;

        return view('bookings.review', compact('concert', 'cartItems', 'totalPrice', 'totalQuantity', 'selectedSeats', 'priceRecords', 'eventTicketTotal', 'bookingPayloadJson'));
    }

    public function checkout(CheckoutBookingRequest $request, Concert $concert)
    {
        $raw = $request->input('booking_items') ?? $request->session()->getOldInput('booking_items');
        if (! is_string($raw) || $raw === '') {
            return redirect()
                ->route('bookings.review', $concert)
                ->withErrors(['general' => 'Please review your tickets before checkout.']);
        }

        $cartItems = json_decode($raw, true);
        if (! is_array($cartItems) || ! array_is_list($cartItems)) {
            return redirect()
                ->route('bookings.create', $concert)
                ->withErrors(['general' => 'Invalid booking data.']);
        }

        $cartTotals = $this->bookingService->calculateTotals($concert, $cartItems);
        $totalQuantity = $cartTotals['totalQuantity'];
        $totalPrice = $cartTotals['totalPrice'];
        $priceRecords = $cartTotals['priceRecords'];

        $concert->load('venue');
        $bookingPayloadJson = json_encode($cartItems);

        return view('bookings.checkout', compact('concert', 'cartItems', 'totalPrice', 'totalQuantity', 'priceRecords', 'bookingPayloadJson'));
    }

    public function confirmPayment(ConfirmBookingPaymentRequest $request, Concert $concert)
    {
        $cartItems = $request->decodedBookingItems();

        $paymentMethod = $request->input('card_number');

        try {
            $booking = $this->bookingService->confirmBooking(Auth::user(), $concert, $cartItems, $paymentMethod);
        } catch (RuntimeException $e) {
            return back()->withErrors(['booking_items' => $e->getMessage()])->withInput();
        }

        if (! $booking) {
            return redirect()
                ->route('bookings.create', $concert)
                ->withErrors(['general' => 'Payment succeeded but booking confirmation could not be loaded. Please check your bookings.']);
        }

        return redirect()->route('bookings.tickets', ['booking' => $booking->id]);
    }

    public function tickets(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        $booking->load('concert.venue', 'tickets.seat', 'payment');

        return view('bookings.tickets', compact('booking'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        $booking->load('concert.venue', 'payment');
        $tickets = $booking->tickets()->with('seat')->paginate(1);

        return view('bookings.show', compact('booking', 'tickets'));
    }
}
