<?php

use App\Models\Profile;
use App\Models\Streak;
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

test('creates a new streak on first update', function () {
    $streak = $this->service->updateStreak($this->user, 'under_budget');

    expect($streak->current_count)->toBe(1);
    expect($streak->longest_count)->toBe(1);
    expect($streak->last_recorded_at->isToday())->toBeTrue();
});

test('increments streak on consecutive days', function () {
    Streak::factory()->for($this->user)->create([
        'type' => 'under_budget',
        'current_count' => 5,
        'longest_count' => 5,
        'last_recorded_at' => now()->subDay(),
    ]);

    $streak = $this->service->updateStreak($this->user, 'under_budget');

    expect($streak->current_count)->toBe(6);
    expect($streak->longest_count)->toBe(6);
});

test('does not double count same day', function () {
    Streak::factory()->for($this->user)->create([
        'type' => 'under_budget',
        'current_count' => 3,
        'longest_count' => 3,
        'last_recorded_at' => now(),
    ]);

    $streak = $this->service->updateStreak($this->user, 'under_budget');

    expect($streak->current_count)->toBe(3);
});

test('resets streak after missed day', function () {
    Streak::factory()->for($this->user)->create([
        'type' => 'under_budget',
        'current_count' => 10,
        'longest_count' => 10,
        'last_recorded_at' => now()->subDays(3),
    ]);

    $streak = $this->service->updateStreak($this->user, 'under_budget');

    expect($streak->current_count)->toBe(1);
    expect($streak->longest_count)->toBe(10);
});

test('break streak sets current to zero', function () {
    Streak::factory()->for($this->user)->create([
        'type' => 'under_budget',
        'current_count' => 5,
        'longest_count' => 8,
        'last_recorded_at' => now()->subDay(),
    ]);

    $this->service->breakStreak($this->user, 'under_budget');

    expect($this->user->streaks()->where('type', 'under_budget')->first()->current_count)->toBe(0);
});

test('tracks longest streak', function () {
    Streak::factory()->for($this->user)->create([
        'type' => 'under_budget',
        'current_count' => 15,
        'longest_count' => 15,
        'last_recorded_at' => now()->subDay(),
    ]);

    $streak = $this->service->updateStreak($this->user, 'under_budget');

    expect($streak->longest_count)->toBe(16);
});
