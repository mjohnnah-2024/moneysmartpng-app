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
        Schema::create('squad_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('squad_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('target_amount', 12, 2);
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('squad_challenges');
    }
};
