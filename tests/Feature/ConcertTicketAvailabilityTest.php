<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertTicketAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function createConcertTicketType(int $quantity = 10): ConcertTicketType
    {
        $venue = Venue::query()->create([
            'name' => 'Availability Arena',
            'location' => 'Pasig',
            'capacity' => 500,
        ]);

        $ticketType = TicketType::query()->create([
            'name' => 'GEN AD',
            'description' => 'General Admission',
        ]);

        $concert = Concert::query()->create([
            'title' => 'Availability Test Concert',
            'description' => 'Test',
            'artist' => 'QA Artist',
            'venue_id' => $venue->id,
            'date' => now()->addDays(10)->toDateString(),
            'time' => '19:00:00',
        ]);

        return ConcertTicketType::query()->create([
            'concert_id' => $concert->id,
            'ticket_type_id' => $ticketType->id,
            'custom_name' => null,
            'price' => 250.00,
            'color' => '#FF6600',
            'quantity' => $quantity,
        ]);
    }

    public function test_available_equals_total_when_no_tickets_sold(): void
    {
        $concertTicketType = $this->createConcertTicketType(12);

        $this->assertSame(0, $concertTicketType->sold_quantity);
        $this->assertSame(12, $concertTicketType->available_quantity);

        $concertTicketType->update(['quantity' => 18]);
        $concertTicketType->refresh();

        $this->assertSame(0, $concertTicketType->sold_quantity);
        $this->assertSame(18, $concertTicketType->available_quantity);
    }

    public function test_available_ignores_prefilled_sold_attribute_and_uses_live_sales(): void
    {
        $concertTicketType = $this->createConcertTicketType(9);

        $booking = Booking::query()->create([
            'user_id' => User::factory()->create()->id,
            'concert_id' => $concertTicketType->concert_id,
            'total_price' => 250.00,
            'status' => 'confirmed',
        ]);

        Ticket::query()->create([
            'booking_id' => $booking->id,
            'concert_ticket_type_id' => $concertTicketType->id,
            'seat_id' => null,
            'ticket_type' => 'GEN AD',
            'price_at_purchase' => 250.00,
            'qr_code' => 'availability-test-1',
        ]);

        $hydratedWithWrongSoldAlias = ConcertTicketType::query()
            ->whereKey($concertTicketType->id)
            ->select('concert_ticket_options.*')
            ->selectRaw('99 as sold_quantity')
            ->firstOrFail();

        $this->assertSame(1, $hydratedWithWrongSoldAlias->sold_quantity);
        $this->assertSame(8, $hydratedWithWrongSoldAlias->available_quantity);
    }
}
