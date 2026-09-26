<?php

namespace Database\Seeders;

use App\Models\Seat;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class SeatSeeder extends Seeder
{
    public function run(): void
    {
        $venues = Venue::all();

        foreach ($venues as $venue) {
            // Base seat counts per section
            $baseSections = [
                'VIP Seated' => 20,
                'Lower Box B (LBB)' => 50,
                'Upper Box B (UBB)' => 100,
                'Lower Box A (LBA)' => 50,
                'Upper Box A (UBA)' => 50,
                'General Admission (Gen Ad)' => 30,
            ];

            $totalBaseSeats = array_sum($baseSections);
            $scale = $venue->capacity / $totalBaseSeats;

            // Scale seat counts to match venue capacity
            $sections = [];
            foreach ($baseSections as $section => $count) {
                $sections[$section] = range(1, (int) round($count * $scale));
            }

            $seats = [];
            foreach ($sections as $section => $seatNumbers) {
                foreach ($seatNumbers as $seatNumber) {
                    $seats[] = [
                        'venue_id' => $venue->id,
                        'seat_number' => (string) $seatNumber,
                        'section' => $section,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert for better performance, in chunks
            $chunks = array_chunk($seats, 1000);
            foreach ($chunks as $chunk) {
                Seat::insert($chunk);
            }
        }
    }
}