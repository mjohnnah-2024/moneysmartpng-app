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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone_number')->nullable();
            $table->string('preferred_language', 5)->default('en');
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->string('plan', 10)->default('free');
            $table->string('referral_code', 6)->unique();
            $table->string('referred_by', 6)->nullable();
            $table->integer('premium_days_earned')->default(0);
            $table->timestamps();

            $table->index('referred_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
