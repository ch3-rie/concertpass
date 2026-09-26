<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function actingAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createVenue(int $capacity = 50): Venue
    {
        return Venue::query()->create([
            'name' => 'Admin Test Venue',
            'location' => 'Test City',
            'capacity' => $capacity,
        ]);
    }

    private function ensureTicketType(string $name = 'GEN AD'): TicketType
    {
        return TicketType::query()->firstOrCreate(
            ['name' => $name],
            ['description' => $name]
        );
    }

    public function test_non_admin_cannot_store_venue(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->from(route('admin.venues.create'))
            ->post(route('admin.venues.store'), [
                'name' => 'X',
                'location' => 'Y',
                'capacity' => 100,
            ])
            ->assertRedirect(route('home'));
    }

    public function test_guest_cannot_store_venue(): void
    {
        $this->from(route('admin.venues.create'))
            ->post(route('admin.venues.store'), [
                'name' => 'X',
                'location' => 'Y',
                'capacity' => 100,
            ])
            ->assertRedirect(route('login'));
    }

    public function test_venue_store_requires_fields_and_positive_capacity(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin)
            ->from(route('admin.venues.create'))
            ->post(route('admin.venues.store'), [
                'name' => '',
                'location' => '',
                'capacity' => 0,
            ])
            ->assertSessionHasErrors(['name', 'location', 'capacity']);
    }

    public function test_venue_store_accepts_valid_payload(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin)
            ->from(route('admin.venues.create'))
            ->post(route('admin.venues.store'), [
                'name' => 'New Hall',
                'location' => 'Makati',
                'capacity' => 25,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.venues.index'));

        $this->assertDatabaseHas('venues', [
            'name' => 'New Hall',
            'location' => 'Makati',
            'capacity' => 25,
        ]);
    }

    public function test_concert_store_rejects_past_date(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);
        $tt = $this->ensureTicketType();

        $this->actingAs($admin)
            ->from(route('admin.concerts.create'))
            ->post(route('admin.concerts.store'), [
                'title' => 'Past Show',
                'description' => null,
                'artist' => 'Band',
                'venue_id' => $venue->id,
                'date' => now()->subDay()->toDateString(),
                'time' => '20:00',
                'ticket_types' => [
                    [
                        'ticket_type_id' => $tt->id,
                        'price' => 100,
                        'quantity' => 10,
                        'color' => '#FF0000',
                    ],
                ],
            ])
            ->assertSessionHasErrors('date');
    }

    public function test_concert_store_rejects_negative_price_and_zero_quantity(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);
        $tt = $this->ensureTicketType();

        $this->actingAs($admin)
            ->from(route('admin.concerts.create'))
            ->post(route('admin.concerts.store'), [
                'title' => 'Show',
                'artist' => 'Band',
                'venue_id' => $venue->id,
                'date' => now()->addMonth()->toDateString(),
                'time' => '20:00',
                'ticket_types' => [
                    [
                        'ticket_type_id' => $tt->id,
                        'price' => -1,
                        'quantity' => 0,
                        'color' => '#GGGGGG',
                    ],
                ],
            ])
            ->assertSessionHasErrors([
                'ticket_types.0.price',
                'ticket_types.0.quantity',
                'ticket_types.0.color',
            ]);
    }

    public function test_concert_store_rejects_when_ticket_total_exceeds_venue_capacity(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);
        $tt = $this->ensureTicketType();

        $this->actingAs($admin)
            ->from(route('admin.concerts.create'))
            ->post(route('admin.concerts.store'), [
                'title' => 'Exceeds Capacity',
                'artist' => 'Band',
                'venue_id' => $venue->id,
                'date' => now()->addMonth()->toDateString(),
                'time' => '20:00',
                'ticket_types' => [
                    [
                        'ticket_type_id' => $tt->id,
                        'price' => 100,
                        'quantity' => 11,
                        'color' => '#FF0000',
                    ],
                ],
            ])
            ->assertSessionHasErrors('ticket_types');
    }

    public function test_concert_store_accepts_when_ticket_total_less_than_venue_capacity(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);
        $tt = $this->ensureTicketType();

        $this->actingAs($admin)
            ->from(route('admin.concerts.create'))
            ->post(route('admin.concerts.store'), [
                'title' => 'Less Than Capacity',
                'artist' => 'Band',
                'venue_id' => $venue->id,
                'date' => now()->addMonth()->toDateString(),
                'time' => '20:00',
                'ticket_types' => [
                    [
                        'ticket_type_id' => $tt->id,
                        'price' => 100,
                        'quantity' => 8,
                        'color' => '#FF0000',
                    ],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.concerts.index'));
    }

    public function test_concert_store_accepts_valid_payload(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);
        $tt = $this->ensureTicketType();

        $this->actingAs($admin)
            ->from(route('admin.concerts.create'))
            ->post(route('admin.concerts.store'), [
                'title' => 'Valid Concert',
                'description' => 'Desc',
                'artist' => 'Band',
                'venue_id' => $venue->id,
                'date' => now()->addMonth()->toDateString(),
                'time' => '20:00',
                'ticket_types' => [
                    [
                        'ticket_type_id' => $tt->id,
                        'price' => 99.50,
                        'quantity' => 10,
                        'color' => '#ABCDEF',
                    ],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.concerts.index'));

        $this->assertDatabaseHas('concerts', [
            'title' => 'Valid Concert',
            'venue_id' => $venue->id,
        ]);
    }

    public function test_concert_store_rejects_invalid_poster_mime(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);
        $tt = $this->ensureTicketType();

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->actingAs($admin)
            ->from(route('admin.concerts.create'))
            ->post(route('admin.concerts.store'), [
                'title' => 'Show',
                'artist' => 'Band',
                'venue_id' => $venue->id,
                'date' => now()->addMonth()->toDateString(),
                'time' => '20:00',
                'poster' => $file,
                'ticket_types' => [
                    [
                        'ticket_type_id' => $tt->id,
                        'price' => 100,
                        'quantity' => 10,
                        'color' => '#FF0000',
                    ],
                ],
            ])
            ->assertSessionHasErrors('poster');
    }

    public function test_admin_user_store_rejects_invalid_email_and_short_password(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'X',
                'email' => 'not-an-email',
                'role' => 'user',
                'password' => 'short',
            ])
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_admin_user_store_rejects_password_longer_than_255_chars(): void
    {
        $admin = $this->actingAdmin();
        $long = str_repeat('a', 256);

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'X',
                'email' => 'longpass@example.com',
                'role' => 'user',
                'password' => $long,
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_admin_user_store_rejects_duplicate_email(): void
    {
        $admin = $this->actingAdmin();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'New',
                'email' => 'taken@example.com',
                'role' => 'user',
                'password' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_user_store_accepts_valid_payload(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'role' => 'user',
                'password' => 'password123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.com',
            'role' => 'user',
        ]);
    }

    public function test_admin_transactions_index_rejects_invalid_status(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin)
            ->from(route('admin.transactions.index'))
            ->get(route('admin.transactions.index', ['status' => 'hacked']))
            ->assertSessionHasErrors('status');
    }

    public function test_api_admin_concert_store_requires_fields(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);

        $this->actingAs($admin)
            ->postJson(route('api.admin.concerts.store'), [
                'title' => '',
                'venue_id' => $venue->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'artist', 'date', 'time']);
    }

    public function test_api_admin_venue_update_rejects_invalid_capacity(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(50);

        $this->actingAs($admin)
            ->putJson(route('api.admin.venues.update', $venue), [
                'name' => $venue->name,
                'location' => $venue->location,
                'capacity' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['capacity']);
    }

    public function test_concert_update_rejects_invalid_ticket_color(): void
    {
        $admin = $this->actingAdmin();
        $venue = $this->createVenue(10);
        $tt = $this->ensureTicketType();

        $concert = Concert::query()->create([
            'title' => 'Existing',
            'description' => null,
            'artist' => 'A',
            'venue_id' => $venue->id,
            'date' => now()->addMonth()->toDateString(),
            'time' => '20:00:00',
        ]);

        $ctt = ConcertTicketType::query()->create([
            'concert_id' => $concert->id,
            'ticket_type_id' => $tt->id,
            'custom_name' => null,
            'price' => 100,
            'color' => '#FF0000',
            'quantity' => 10,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.concerts.edit', $concert))
            ->put(route('admin.concerts.update', $concert), [
                '_method' => 'PUT',
                'title' => 'Existing',
                'description' => null,
                'artist' => 'A',
                'venue_id' => $venue->id,
                'date' => now()->addMonth()->toDateString(),
                'time' => '20:00',
                'ticket_types' => [
                    [
                        'id' => $ctt->id,
                        'ticket_type_id' => $tt->id,
                        'price' => 100,
                        'quantity' => 10,
                        'color' => 'red',
                    ],
                ],
            ])
            ->assertSessionHasErrors('ticket_types.0.color');
    }
}
