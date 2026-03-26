<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Profile;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Profile::factory()->for($user)->create([
            'full_name' => 'John Kila',
            'monthly_income' => 3500.00,
        ]);

        Transaction::factory(15)->for($user)->expense()->create();
        Transaction::factory(5)->for($user)->income()->create();

        $budgetCategories = ['Food/Market', 'Transport PMV', 'Airtime/Data', 'Rent'];
        foreach ($budgetCategories as $category) {
            Budget::factory()->for($user)->create(['category' => $category]);
        }

        Goal::factory(2)->for($user)->create();
        Goal::factory(1)->for($user)->completed()->create();
    }
}
