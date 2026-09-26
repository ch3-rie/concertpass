<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('concert_ticket_type_id')
                ->nullable()
                ->constrained('concert_ticket_options')
                ->nullOnDelete()
                ->after('booking_id');
            $table->string('ticket_type');
            $table->decimal('price_at_purchase', 10, 2);
            $table->string('qr_code')->nullable();
            $table->foreignId('seat_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
