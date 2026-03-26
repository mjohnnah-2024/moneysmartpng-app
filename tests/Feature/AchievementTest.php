<?php

use App\Models\Achievement;
use App\Models\Budget;
use App\Models\Goal;
use App\Models\Profile;
use App\Models\Streak;
use App\Models\Transaction;
use App\Models\User;
use App\Services\StreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->profile = Profile::factory()->for($this->user)->create([
        'monthly_income' => 3000.00,
    ]);
    $this->actingAs($this->user);
    $this->service = new StreakService;
});

test('awards first_transaction badge', function () {
    Transaction::factory()->for($this->user)->create();

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->toContain('first_transaction');
    $this->assertDatabaseHas('achievements', [
        'user_id' => $this->user->id,
        'badge_key' => 'first_transaction',
    ]);
});

test('awards budget_creator badge', function () {
    Budget::factory()->for($this->user)->create();

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->toContain('budget_creator');
});

test('awards goal_setter badge', function () {
    Goal::factory()->for($this->user)->create();

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->toContain('goal_setter');
});

test('awards goal_completed badge', function () {
    Goal::factory()->for($this->user)->create(['status' => 'completed']);

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->toContain('goal_completed');
});

test('awards 3_day_streak badge', function () {
    Streak::factory()->for($this->user)->create([
        'type' => 'under_budget',
        'current_count' => 3,
        'longest_count' => 3,
    ]);

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->toContain('3_day_streak');
});

test('awards 7_day_streak badge', function () {
    Streak::factory()->for($this->user)->create([
        'type' => 'under_budget',
        'current_count' => 7,
        'longest_count' => 7,
    ]);

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->toContain('7_day_streak');
});

test('does not award duplicate badges', function () {
    Transaction::factory()->for($this->user)->create();
    Achievement::factory()->for($this->user)->create([
        'badge_key' => 'first_transaction',
        'points' => 10,
    ]);

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->not->toContain('first_transaction');
    expect($this->user->achievements()->where('badge_key', 'first_transaction')->count())->toBe(1);
});

test('awards 10_transactions badge', function () {
    Transaction::factory()->for($this->user)->count(10)->create();

    $awarded = $this->service->checkAchievements($this->user);

    expect($awarded)->toContain('10_transactions');
});

test('get badges returns all definitions with earned status', function () {
    Transaction::factory()->for($this->user)->create();
    $this->service->checkAchievements($this->user);

    $badges = $this->service->getBadgesForUser($this->user);

    expect($badges)->toHaveCount(count(StreakService::BADGE_DEFINITIONS));

    $firstTx = collect($badges)->firstWhere('badge_key', 'first_transaction');
    expect($firstTx['earned_at'])->not->toBeNull();

    $streak30 = collect($badges)->firstWhere('badge_key', '30_day_streak');
    expect($streak30['earned_at'])->toBeNull();
});

test('achievements page is accessible', function () {
    $response = $this->get(route('achievements'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('achievements')
        ->has('badges')
        ->has('totalPoints')
        ->has('streaks')
    );
});

test('total points are calculated correctly', function () {
    Transaction::factory()->for($this->user)->create();
    Budget::factory()->for($this->user)->create();
    $this->service->checkAchievements($this->user);

    $points = $this->service->getTotalPoints($this->user);

    // first_transaction (10) + budget_creator (10)
    expect($points)->toBe(20);
});
