<?php

namespace App\Services;

use App\Models\ConcertTicketType;
use App\Models\Seat;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

/**
 * Ensures each venue section has enough physical seat rows for seated ticket types.
 * Venue seeding scales sections proportionally, which can yield fewer rows than an
 * admin-defined quantity; this grows the pool so dropdown count matches ticket qty.
 */
class VenueSeatPoolService
{
    public function __construct(
        private readonly ConcertSeatAvailabilityService $seatAvailability,
    ) {
    }

    /**
     * For each seated allocation, ensure seats.section has at least `quantity` rows.
     *
     * @param  iterable<ConcertTicketType>  $concertTicketTypes
     */
    public function syncPhysicalSeatsForVenueTicketTypes(Venue $venue, iterable $concertTicketTypes): void
    {
        foreach ($concertTicketTypes as $ctt) {
            if (! $ctt instanceof ConcertTicketType) {
                continue;
            }

            $slug = $ctt->ticketType->name ?? '';
            if (! $this->seatAvailability->requiresSeatSelection($slug)) {
                continue;
            }

            $section = $this->seatAvailability->sectionForTicketTypeSlug($slug);
            if ($section === null) {
                continue;
            }

            $needed = max(0, (int) $ctt->quantity);
            $this->growSectionToCount((int) $venue->id, $section, $needed);
        }
    }

    private function growSectionToCount(int $venueId, string $section, int $requiredCount): void
    {
        if ($requiredCount <= 0) {
            return;
        }

        $currentCount = Seat::query()
            ->where('venue_id', $venueId)
            ->where('section', $section)
            ->count();

        if ($currentCount >= $requiredCount) {
            return;
        }

        $toInsert = $requiredCount - $currentCount;

        $maxNum = (int) DB::table('seats')
            ->where('venue_id', $venueId)
            ->where('section', $section)
            ->selectRaw('COALESCE(MAX(CAST(seat_number AS UNSIGNED)), 0) as mx')
            ->value('mx');

        $now = now();
        $batch = [];

        for ($i = 1; $i <= $toInsert; $i++) {
            $num = $maxNum + $i;
            $batch[] = [
                'venue_id' => $venueId,
                'section' => $section,
                'seat_number' => (string) $num,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('seats')->insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('seats')->insert($batch);
        }
    }
}
