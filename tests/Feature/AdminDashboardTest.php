<?php

use App\Models\ChatMessage;
use App\Models\ManualPayment;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    Profile::factory()->for($this->admin)->create();
});

test('admin can access the dashboard', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/dashboard'));
});

test('non-admin is forbidden from the dashboard', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('guest is redirected to login', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('dashboard shows correct total users count', function () {
    User::factory()->count(5)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $response->assertOk();
    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    // 5 created + 1 admin = 6
    expect($stats['totalUsers'])->toBe(6);
});

test('dashboard shows correct new today count', function () {
    User::factory()->count(3)->create();
    User::factory()->create(['created_at' => now()->subDay()]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    // 3 new today + 1 admin (also today) = 4
    expect($stats['newToday'])->toBe(4);
});

test('dashboard shows correct active premium count', function () {
    $user = User::factory()->create();
    Subscription::factory()->active()->for($user)->create();

    $expired = User::factory()->create();
    Subscription::factory()->expired()->for($expired)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    expect($stats['activePremium'])->toBe(1);
});

test('dashboard shows ai message count', function () {
    $user = User::factory()->create();
    ChatMessage::factory()->count(10)->for($user)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    expect($stats['aiMessages'])->toBe(10);
});

test('dashboard shows estimated revenue', function () {
    $user1 = User::factory()->create();
    Subscription::factory()->for($user1)->create(['payment_method' => 'stripe', 'status' => 'active']);

    $user2 = User::factory()->create();
    ManualPayment::factory()->approved()->for($user2)->create(['amount' => 24.99]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $stats = $response->getOriginalContent()->getData()['page']['props']['stats'];
    // 1 stripe ($9.99) + $24.99 mobile = $34.98
    expect($stats['estimatedRevenue'])->toBe(34.98);
});

test('dashboard includes recent activity feed', function () {
    User::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $activity = $response->getOriginalContent()->getData()['page']['props']['recentActivity'];
    expect($activity)->not->toBeEmpty();
});

test('dashboard includes daily signups data', function () {
    User::factory()->count(2)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $signups = $response->getOriginalContent()->getData()['page']['props']['dailySignups'];
    expect($signups)->not->toBeEmpty();
});
