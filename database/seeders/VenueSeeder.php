<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        Venue::insert([
            [
                'name' => 'Madison Square Garden',
                'location' => 'New York, NY',
                'capacity' => 20000,
            ],
            [
                'name' => 'Araneta Coliseum',
                'location' => 'Quezon City, Manila, Philippines',
                'capacity' => 25000,
            ],
            [
                'name' => 'SM Mall of Asia Arena',
                'location' => 'Pasay City, Philippines',
                'capacity' => 20000,
            ],
            [
                'name' => 'Philippine Arena',
                'location' => 'Bulacan, Philippines',
                'capacity' => 55000,
            ],
            [
                'name' => 'Cultural Center of the Philippines',
                'location' => 'Pasay City, Philippines',
                'capacity' => 1800,
            ],
            [
                'name' => 'Ynares Center',
                'location' => 'Antipolo, Philippines',
                'capacity' => 7000,
            ],
            [
                'name' => 'Clark International Convention Center',
                'location' => 'Clark, Pampanga, Philippines',
                'capacity' => 5000,
            ],
            [
                'name' => 'Smart Araneta Coliseum',
                'location' => 'Quezon City, Philippines',
                'capacity' => 16000,
            ],
        ]);
    }
}