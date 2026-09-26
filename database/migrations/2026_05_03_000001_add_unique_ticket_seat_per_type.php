<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prevents the same physical seat from being sold twice for one ticket option (double booking).
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unique(
                ['concert_ticket_type_id', 'seat_id'],
                'tickets_concert_ticket_type_seat_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique('tickets_concert_ticket_type_seat_unique');
        });
    }
};
