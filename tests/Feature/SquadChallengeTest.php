<?php

use App\Models\Profile;
use App\Models\Squad;
use App\Models\SquadChallenge;
use App\Models\SquadChallengeProgress;
use App\Models\SquadMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->profile = Profile::factory()->for($this->user)->create();
    $this->squad = Squad::factory()->create(['creator_id' => $this->user->id]);
    SquadMember::factory()->admin()->create([
        'squad_id' => $this->squad->id,
        'user_id' => $this->user->id,
    ]);
    $this->actingAs($this->user);
});

test('admin can create a challenge', function () {
    $response = $this->post("/squads/{$this->squad->id}/challenges", [
        'name' => 'Save K100',
        'target_amount' => 100,
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addDays(7)->toDateString(),
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('squad_challenges', [
        'squad_id' => $this->squad->id,
        'name' => 'Save K100',
    ]);
});

test('non-admin cannot create a challenge', function () {
    $member = User::factory()->create();
    Profile::factory()->for($member)->create();
    SquadMember::factory()->create([
        'squad_id' => $this->squad->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    $response = $this->actingAs($member)->post("/squads/{$this->squad->id}/challenges", [
        'name' => 'Save K100',
        'target_amount' => 100,
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addDays(7)->toDateString(),
    ]);

    $response->assertForbidden();
});

test('member can view a challenge', function () {
    $challenge = SquadChallenge::factory()->create(['squad_id' => $this->squad->id]);

    $response = $this->get("/squads/{$this->squad->id}/challenges/{$challenge->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('squads/challenge'));
});

test('non-member cannot view a challenge', function () {
    $otherUser = User::factory()->create();
    Profile::factory()->for($otherUser)->create();
    $challenge = SquadChallenge::factory()->create(['squad_id' => $this->squad->id]);

    $response = $this->actingAs($otherUser)->get("/squads/{$this->squad->id}/challenges/{$challenge->id}");

    $response->assertForbidden();
});

test('member can update their progress', function () {
    $challenge = SquadChallenge::factory()->create([
        'squad_id' => $this->squad->id,
        'target_amount' => 200,
    ]);

    $response = $this->post("/squads/{$this->squad->id}/challenges/{$challenge->id}/progress", [
        'amount' => 50.00,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('squad_challenge_progress', [
        'challenge_id' => $challenge->id,
        'user_id' => $this->user->id,
        'current_amount' => 50.00,
    ]);
});

test('progress shows percentages not raw amounts', function () {
    $challenge = SquadChallenge::factory()->create([
        'squad_id' => $this->squad->id,
        'target_amount' => 200,
    ]);
    SquadChallengeProgress::factory()->create([
        'challenge_id' => $challenge->id,
        'user_id' => $this->user->id,
        'current_amount' => 100,
    ]);

    $response = $this->get("/squads/{$this->squad->id}/challenges/{$challenge->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('squads/challenge')
        ->where('leaderboard.0.percentage', 50)
        ->missing('leaderboard.0.current_amount')
    );
});

test('challenge requires valid dates', function () {
    $response = $this->post("/squads/{$this->squad->id}/challenges", [
        'name' => 'Bad dates',
        'target_amount' => 100,
        'starts_at' => now()->addDays(5)->toDateString(),
        'ends_at' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('ends_at');
});

test('challenge requires a name', function () {
    $response = $this->post("/squads/{$this->squad->id}/challenges", [
        'name' => '',
        'target_amount' => 100,
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addDays(7)->toDateString(),
    ]);

    $response->assertSessionHasErrors('name');
});

test('progress amount must be positive', function () {
    $challenge = SquadChallenge::factory()->create(['squad_id' => $this->squad->id]);

    $response = $this->post("/squads/{$this->squad->id}/challenges/{$challenge->id}/progress", [
        'amount' => 0,
    ]);

    $response->assertSessionHasErrors('amount');
});
