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
        Schema::table('profiles', function (Blueprint $table) {
            $table->enum('pay_cycle_type', ['monthly', 'fortnightly', 'weekly'])->default('monthly')->after('monthly_income');
            $table->unsignedTinyInteger('pay_cycle_start_day')->default(1)->after('pay_cycle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['pay_cycle_type', 'pay_cycle_start_day']);
        });
    }
};
