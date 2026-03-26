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
        Schema::create('squad_challenge_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('squad_challenges')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['challenge_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('squad_challenge_progress');
    }
};
