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
        Schema::create('agent_traces', function (Blueprint $table) {
            $table->id();
            $table->string('run_id', 36)->index();
            $table->string('agent_name', 40);
            $table->tinyInteger('sequence');
            $table->json('input_payload');
            $table->json('output_payload');
            $table->text('reasoning');
            $table->decimal('confidence', 3, 2)->default(0);
            $table->integer('duration_ms')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_traces');
    }
};
