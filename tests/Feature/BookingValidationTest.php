<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createConcertWithGenAdOnly(): array
    {
        $venue = Venue::query()->create([
            'name' => 'Test Arena',
            'location' => 'Manila',
            'capacity' => 10,
        ]);

        foreach (range(1, 10) as $n) {
            Seat::query()->create([
                'venue_id' => $venue->id,
                'section' => 'General Admission (Gen Ad)',
                'seat_number' => (string) $n,
            ]);
        }

        $genAd = TicketType::query()->firstOrCreate(
            ['name' => 'GEN AD'],
            ['description' => 'General Admission']
        );

        $concert = Concert::query()->create([
            'title' => 'Validation Test Show',
            'description' => null,
            'artist' => 'Tester',
            'venue_id' => $venue->id,
            'date' => now()->addMonth()->toDateString(),
            'time' => '20:00:00',
        ]);

        $ctt = ConcertTicketType::query()->create([
            'concert_id' => $concert->id,
            'ticket_type_id' => $genAd->id,
            'custom_name' => null,
            'price' => 100.00,
            'color' => '#F4A460',
            'quantity' => 10,
        ]);

        return [$venue, $concert, $ctt];
    }

    private function createConcertWithVipSeated(int $vipQty = 3): array
    {
        $venue = Venue::query()->create([
            'name' => 'Seated Arena',
            'location' => 'Makati',
            'capacity' => 20,
        ]);

        foreach (range(1, 10) as $n) {
            Seat::query()->create([
                'venue_id' => $venue->id,
                'section' => 'General Admission (Gen Ad)',
                'seat_number' => (string) $n,
            ]);
        }
        foreach (range(1, $vipQty) as $n) {
            Seat::query()->create([
                'venue_id' => $venue->id,
                'section' => 'VIP Seated',
                'seat_number' => (string) $n,
            ]);
        }

        $genAd = TicketType::query()->firstOrCreate(
            ['name' => 'GEN AD'],
            ['description' => 'General Admission']
        );
        $vipSeated = TicketType::query()->firstOrCreate(
            ['name' => 'VIP Seated'],
            ['description' => 'VIP Seated']
        );

        $concert = Concert::query()->create([
            'title' => 'Seated Validation Show',
            'description' => null,
            'artist' => 'Tester',
            'venue_id' => $venue->id,
            'date' => now()->addMonth()->toDateString(),
            'time' => '20:00:00',
        ]);

        $cttGen = ConcertTicketType::query()->create([
            'concert_id' => $concert->id,
            'ticket_type_id' => $genAd->id,
            'custom_name' => null,
            'price' => 50.00,
            'color' => '#F4A460',
            'quantity' => 10,
        ]);
        $cttVip = ConcertTicketType::query()->create([
            'concert_id' => $concert->id,
            'ticket_type_id' => $vipSeated->id,
            'custom_name' => null,
            'price' => 500.00,
            'color' => '#FFD700',
            'quantity' => $vipQty,
        ]);

        $vipSeats = Seat::query()
            ->where('venue_id', $venue->id)
            ->where('section', 'VIP Seated')
            ->orderByRaw('CAST(seat_number AS UNSIGNED) ASC')
            ->get();

        return [$venue, $concert, $cttGen, $cttVip, $vipSeats];
    }

    /**
     * @return array<string, string>
     */
    private function genAdBookingItemsField(ConcertTicketType $ctt): array
    {
        return [
            'booking_items' => json_encode([
                [
                    'concert_ticket_type_id' => $ctt->id,
                    'quantity' => 1,
                    'ticket_type' => 'GEN AD',
                ],
            ]),
        ];
    }

    public function test_confirm_payment_rejects_non_digit_card_number(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => 'abcdefghijklm',
                'expiry' => '12/30',
                'cvv' => '123',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '1',
            ]))
            ->assertSessionHasErrors('card_number');
    }

    public function test_confirm_payment_rejects_card_number_with_letters_mixed_in_digits(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '4111a11111111111',
                'expiry' => '12/30',
                'cvv' => '123',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '1',
            ]))
            ->assertSessionHasErrors('card_number');
    }

    public function test_confirm_payment_rejects_card_with_too_few_digits(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '12345678901',
                'expiry' => '12/30',
                'cvv' => '123',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '1',
            ]))
            ->assertSessionHasErrors('card_number');
    }

    public function test_confirm_payment_rejects_invalid_expiry(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '4111111111111111',
                'expiry' => '13/30',
                'cvv' => '123',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '1',
            ]))
            ->assertSessionHasErrors('expiry');
    }

    public function test_confirm_payment_rejects_expired_card_expiry(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01', 'UTC'));

        try {
            [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
            $user = User::factory()->create();

            $this->actingAs($user)
                ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                    'card_number' => '4111111111111111',
                    'expiry' => '05/26',
                    'cvv' => '123',
                    'cardholder_name' => 'Juan Cruz',
                    'terms' => '1',
                ]))
                ->assertSessionHasErrors('expiry');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_confirm_payment_rejects_invalid_luhn_checksum(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '4111111111111112',
                'expiry' => '12/30',
                'cvv' => '123',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '1',
            ]))
            ->assertSessionHasErrors('card_number');
    }

    public function test_confirm_payment_rejects_invalid_cvv(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '4111111111111111',
                'expiry' => '12/30',
                'cvv' => '12',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '1',
            ]))
            ->assertSessionHasErrors('cvv');
    }

    public function test_confirm_payment_rejects_cardholder_with_digits(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '4111111111111111',
                'expiry' => '12/30',
                'cvv' => '123',
                'cardholder_name' => 'John123',
                'terms' => '1',
            ]))
            ->assertSessionHasErrors('cardholder_name');
    }

    public function test_confirm_payment_requires_terms(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '4111111111111111',
                'expiry' => '12/30',
                'cvv' => '123',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '0',
            ]))
            ->assertSessionHasErrors('terms');
    }

    public function test_confirm_payment_succeeds_with_valid_payload(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.confirm-payment', $concert), array_merge($this->genAdBookingItemsField($ctt), [
                'card_number' => '4111111111111111',
                'expiry' => '12/30',
                'cvv' => '123',
                'cardholder_name' => 'Juan Cruz',
                'terms' => '1',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'concert_id' => $concert->id,
        ]);
    }

    public function test_store_booking_rejects_invalid_json_cart(): void
    {
        [, $concert] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.store', $concert), [
                'booking_items' => 'not-valid-json{{{',
            ])
            ->assertSessionHasErrors('booking_items');
    }

    public function test_store_booking_rejects_empty_cart_payload(): void
    {
        [, $concert] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.store', $concert), [
                'booking_items' => '[]',
            ])
            ->assertSessionHasErrors('booking_items');
    }

    public function test_store_booking_accepts_valid_gen_ad_cart(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $cart = json_encode([
            [
                'concert_ticket_type_id' => $ctt->id,
                'quantity' => 1,
                'ticket_type' => 'GEN AD',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('bookings.store', $concert), [
                'booking_items' => $cart,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('bookings.review', $concert));
    }

    public function test_get_seats_returns_422_for_ticket_option_from_another_concert(): void
    {
        [, $concertA] = $this->createConcertWithGenAdOnly();

        [, , , $cttVipB] = $this->createConcertWithVipSeated(3);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('bookings.seats', $concertA).'?concert_ticket_type_id='.$cttVipB->id)
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid ticket option for this event.',
            ]);
    }

    public function test_get_seats_redirects_with_error_when_ticket_type_missing(): void
    {
        [, $concert] = $this->createConcertWithGenAdOnly();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('bookings.create', $concert))
            ->get(route('bookings.seats', $concert))
            ->assertRedirect(route('bookings.create', $concert))
            ->assertSessionHasErrors('concert_ticket_type_id');
    }

    public function test_cart_validation_rejects_duplicate_seat_selection(): void
    {
        [, $concert, , $vipCtt, $vipSeats] = $this->createConcertWithVipSeated(3);

        $seatId = (int) $vipSeats->first()->id;

        $service = app(BookingValidationService::class);

        $error = $service->validateBookingItems($concert, [
            [
                'concert_ticket_type_id' => $vipCtt->id,
                'seat_id' => $seatId,
            ],
            [
                'concert_ticket_type_id' => $vipCtt->id,
                'seat_id' => $seatId,
            ],
        ]);

        $this->assertNotNull($error);
        $this->assertStringContainsStringIgnoringCase('once', $error);
    }

    public function test_cart_validation_rejects_more_than_max_tickets_per_transaction(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();
        $service = app(BookingValidationService::class);

        $error = $service->validateBookingItems($concert, [
            [
                'concert_ticket_type_id' => $ctt->id,
                'quantity' => BookingValidationService::MAX_TICKETS_PER_TRANSACTION + 1,
            ],
        ]);

        $this->assertNotNull($error);
    }

    public function test_cart_validation_rejects_when_type_quantity_exceeds_remaining(): void
    {
        [, $concert, $ctt] = $this->createConcertWithGenAdOnly();

        foreach (range(1, 10) as $_) {
            $booking = Booking::query()->create([
                'user_id' => User::factory()->create()->id,
                'concert_id' => $concert->id,
                'total_price' => 100,
                'status' => 'confirmed',
            ]);
            Ticket::query()->create([
                'booking_id' => $booking->id,
                'concert_ticket_type_id' => $ctt->id,
                'seat_id' => null,
                'ticket_type' => 'GEN AD',
                'price_at_purchase' => 100,
                'qr_code' => 't'.uniqid(),
            ]);
        }

        $service = app(BookingValidationService::class);
        $error = $service->validateBookingItems($concert, [
            [
                'concert_ticket_type_id' => $ctt->id,
                'quantity' => 1,
                'ticket_type' => 'GEN AD',
            ],
        ]);

        $this->assertNotNull($error);
    }
}
