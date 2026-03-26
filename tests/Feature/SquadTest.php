<?php

use App\Models\Profile;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->profile = Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('user can view squads index', function () {
    $response = $this->get('/squads');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('squads/index'));
});

test('user can create a squad', function () {
    $response = $this->post('/squads', [
        'name' => 'Test Squad',
        'description' => 'A test squad',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('squads', [
        'name' => 'Test Squad',
        'creator_id' => $this->user->id,
    ]);
    $this->assertDatabaseHas('squad_members', [
        'user_id' => $this->user->id,
        'role' => 'admin',
    ]);
});

test('squad creation limited for free users', function () {
    Squad::factory()->count(2)->create(['creator_id' => $this->user->id]);

    $response = $this->post('/squads', [
        'name' => 'Third Squad',
    ]);

    $response->assertForbidden();
});

test('user can join a squad with valid invite code', function () {
    $squad = Squad::factory()->create();

    $response = $this->post('/squads/join', [
        'invite_code' => $squad->invite_code,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('squad_members', [
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
        'role' => 'member',
    ]);
});

test('user cannot join with invalid invite code', function () {
    $response = $this->post('/squads/join', [
        'invite_code' => 'INVALID1',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Invalid invite code.');
});

test('user cannot join a full squad', function () {
    $squad = Squad::factory()->create(['max_members' => 2]);
    SquadMember::factory()->count(2)->create(['squad_id' => $squad->id]);

    $response = $this->post('/squads/join', [
        'invite_code' => $squad->invite_code,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'This squad is full.');
});

test('user cannot join the same squad twice', function () {
    $squad = Squad::factory()->create();
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->post('/squads/join', [
        'invite_code' => $squad->invite_code,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'You are already a member of this squad.');
});

test('member can view squad detail', function () {
    $squad = Squad::factory()->create();
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->get("/squads/{$squad->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('squads/show'));
});

test('non-member cannot view squad', function () {
    $squad = Squad::factory()->create();

    $response = $this->get("/squads/{$squad->id}");

    $response->assertForbidden();
});

test('member can leave a squad', function () {
    $squad = Squad::factory()->create();
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
        'role' => 'member',
    ]);

    $response = $this->post("/squads/{$squad->id}/leave");

    $response->assertRedirect('/squads');
    $this->assertDatabaseMissing('squad_members', [
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
    ]);
});

test('creator cannot leave their own squad', function () {
    $squad = Squad::factory()->create(['creator_id' => $this->user->id]);
    SquadMember::factory()->admin()->create([
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->post("/squads/{$squad->id}/leave");

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('creator can delete squad', function () {
    $squad = Squad::factory()->create(['creator_id' => $this->user->id]);
    SquadMember::factory()->admin()->create([
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->delete("/squads/{$squad->id}");

    $response->assertRedirect('/squads');
    $this->assertDatabaseMissing('squads', ['id' => $squad->id]);
});

test('non-creator cannot delete squad', function () {
    $squad = Squad::factory()->create();
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->delete("/squads/{$squad->id}");

    $response->assertForbidden();
});

test('squad name is required', function () {
    $response = $this->post('/squads', [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
});
