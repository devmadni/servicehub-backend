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
        Schema::create('pricing_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->constrained();
            $table->integer('base_rate');
            $table->integer('visit_fee')->default(150);
            $table->integer('distance_cost')->default(0);
            $table->integer('urgency_adj')->default(0);
            $table->decimal('surge_factor', 4, 2)->default(1.00);
            $table->integer('loyalty_discount')->default(0);
            $table->integer('total');
            $table->integer('provider_net');
            $table->boolean('is_budget_tier')->default(false);
            $table->foreignId('alt_quote_id')->nullable()->constrained('pricing_quotes')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_quotes');
    }
};
