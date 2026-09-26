<?php

use App\Models\Concert;
use App\Services\VenueSeatPoolService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('venue:sync-seat-pools', function () {
    $pool = app(VenueSeatPoolService::class);
    $synced = 0;
    Concert::query()->with(['venue', 'concertTicketTypes.ticketType'])->chunkById(50, function ($concerts) use ($pool, &$synced) {
        foreach ($concerts as $concert) {
            if (! $concert->venue) {
                continue;
            }
            $pool->syncPhysicalSeatsForVenueTicketTypes($concert->venue, $concert->concertTicketTypes);
            $synced++;
        }
    });
    $this->info("Synced physical seat pools for {$synced} concerts.");
})->purpose('Ensure venue seat rows match admin ticket quantities for seated types');
