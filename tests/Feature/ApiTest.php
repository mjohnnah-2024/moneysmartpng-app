<?php

use App\Models\Profile;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->profile = Profile::factory()->for($this->user)->create();
});

test('unauthenticated request returns 401', function () {
    $this->getJson('/api/v1/safe-to-spend')->assertUnauthorized();
    $this->getJson('/api/v1/recommendations')->assertUnauthorized();
    $this->getJson('/api/v1/streaks')->assertUnauthorized();
    $this->getJson('/api/v1/achievements')->assertUnauthorized();
    $this->getJson('/api/v1/squads')->assertUnauthorized();
});

test('safe-to-spend endpoint returns json', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/v1/safe-to-spend');

    $response->assertOk()
        ->assertJsonStructure([
            'daily',
            'remaining',
            'percentage',
            'status',
            'breakdown' => ['availableBalance', 'pendingBills', 'goalContributions'],
        ]);
});

test('recommendations endpoint returns json array', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/v1/recommendations');

    $response->assertOk()->assertJsonIsArray();
});

test('streaks endpoint returns user streaks', function () {
    Sanctum::actingAs($this->user);

    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'under_budget',
        'current_count' => 5,
        'longest_count' => 10,
    ]);

    $response = $this->getJson('/api/v1/streaks');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'type' => 'under_budget',
            'current_count' => 5,
            'longest_count' => 10,
        ]);
});

test('achievements endpoint returns badges and total points', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/v1/achievements');

    $response->assertOk()
        ->assertJsonStructure(['badges', 'totalPoints']);
});

test('squads endpoint returns user squads', function () {
    Sanctum::actingAs($this->user);

    $squad = Squad::factory()->create(['creator_id' => $this->user->id]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
        'role' => 'admin',
    ]);

    $response = $this->getJson('/api/v1/squads');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'name' => $squad->name,
            'role' => 'admin',
        ]);
});
