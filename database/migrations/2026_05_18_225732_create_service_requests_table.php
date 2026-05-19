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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->text('raw_input');
            $table->enum('detected_lang', ['urdu', 'roman_urdu', 'english', 'mixed'])->default('english');
            $table->string('service_type', 80)->nullable();
            $table->string('location', 120)->nullable();
            $table->decimal('user_lat', 10, 7)->nullable();
            $table->decimal('user_lng', 10, 7)->nullable();
            $table->enum('urgency', ['low', 'normal', 'high', 'emergency'])->default('normal');
            $table->enum('budget_sensitivity', ['normal', 'high'])->default('normal');
            $table->string('requested_datetime', 80)->nullable();
            $table->enum('issue_severity', ['low', 'medium', 'high'])->default('low');
            $table->decimal('confidence', 3, 2)->default(0);
            $table->text('clarification_asked')->nullable();
            $table->enum('complexity', ['basic', 'intermediate', 'complex'])->default('basic');
            $table->string('agent_run_id', 36)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
