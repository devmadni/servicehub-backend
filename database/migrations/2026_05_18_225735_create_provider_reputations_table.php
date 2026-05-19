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
        Schema::create('provider_reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained();
            $table->decimal('score_before', 5, 4);
            $table->decimal('score_after', 5, 4);
            $table->enum('trigger', ['feedback', 'dispute', 'no_show', 'cancellation']);
            $table->foreignId('booking_id')->constrained();
            $table->decimal('delta', 5, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_reputations');
    }
};
