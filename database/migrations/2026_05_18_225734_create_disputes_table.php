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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->enum('trigger_type', ['no_show', 'late', 'quality', 'price', 'damage', 'refund']);
            $table->text('description');
            $table->tinyInteger('stage')->default(1);
            $table->text('resolution_offer')->nullable();
            $table->enum('outcome', ['resolved', 'refunded', 'escalated', 'blacklisted'])->nullable();
            $table->boolean('human_flag')->default(false);
            $table->integer('refund_amount')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
