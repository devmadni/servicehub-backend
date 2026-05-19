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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained();
            $table->string('name', 120);
            $table->string('area', 80);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->decimal('on_time_score', 5, 2)->default(100);
            $table->decimal('cancel_rate', 5, 2)->default(0);
            $table->tinyInteger('experience_years')->default(1);
            $table->json('specializations')->nullable();
            $table->tinyInteger('capacity_current')->default(0);
            $table->decimal('risk_score', 3, 2)->default(0);
            $table->integer('price_min')->default(500);
            $table->enum('status', ['active', 'suspended', 'blacklisted'])->default('active');
            $table->tinyInteger('warning_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
