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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref', 12)->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('provider_id')->constrained();
            $table->foreignId('service_request_id')->constrained();
            $table->foreignId('pricing_quote_id')->nullable()->constrained('pricing_quotes')->nullOnDelete();
            $table->dateTime('slot_datetime');
            $table->dateTime('slot_end_datetime')->nullable();
            $table->enum('complexity', ['basic', 'intermediate', 'complex'])->default('basic');
            $table->enum('status', ['pending', 'confirmed', 'en_route', 'completed', 'disputed', 'cancelled'])->default('pending');
            $table->boolean('simulated')->default(true);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('agent_run_id', 36)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
