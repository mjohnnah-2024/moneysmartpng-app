<?php

use App\Models\ChatMessage;
use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    Profile::factory()->for($this->admin)->create();
});

test('admin can access AI usage page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/ai-usage'));
});

test('non-admin cannot access AI usage page', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('admin.ai-usage'))
        ->assertForbidden();
});

test('AI usage shows correct total message count', function () {
    $user = User::factory()->create();
    ChatMessage::factory()->count(15)->for($user)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    expect($stats['totalMessages'])->toBe(15);
});

test('AI usage shows correct today message count', function () {
    $user = User::factory()->create();
    ChatMessage::factory()->count(5)->for($user)->create();
    ChatMessage::factory()->for($user)->create(['created_at' => now()->subDays(2)]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    expect($stats['todayMessages'])->toBe(5);
});

test('AI usage shows estimated cost', function () {
    $user = User::factory()->create();
    ChatMessage::factory()->count(100)->for($user)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    // 100 * $0.002 = $0.20
    expect($stats['estimatedCost'])->toBe(0.2);
});

test('AI usage daily alert is false when below threshold', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    expect($stats['dailyAlert'])->toBeFalse();
});

test('AI usage includes top users', function () {
    $user1 = User::factory()->create(['name' => 'Top Chatter']);
    ChatMessage::factory()->count(10)->for($user1)->create();

    $user2 = User::factory()->create(['name' => 'Lesser Chatter']);
    ChatMessage::factory()->count(3)->for($user2)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'));

    $topUsers = $response->getOriginalContent()->getData()['page']['props']['topUsers'];
    expect($topUsers)->not->toBeEmpty();
    expect($topUsers[0]['name'])->toBe('Top Chatter');
    expect($topUsers[0]['message_count'])->toBe(10);
});

test('AI usage includes hourly volume data', function () {
    $user = User::factory()->create();
    ChatMessage::factory()->count(5)->for($user)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'));

    $hourly = $response->getOriginalContent()->getData()['page']['props']['hourlyVolume'];
    expect($hourly)->not->toBeEmpty();
});

test('AI usage includes daily totals data', function () {
    $user = User::factory()->create();
    ChatMessage::factory()->count(5)->for($user)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.ai-usage'));

    $daily = $response->getOriginalContent()->getData()['page']['props']['dailyTotals'];
    expect($daily)->not->toBeEmpty();
});
