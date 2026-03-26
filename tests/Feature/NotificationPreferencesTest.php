<?php

use App\Models\NotificationPreference;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->profile = Profile::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

test('can view notification preferences page', function () {
    $response = $this->get(route('notifications.edit'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('settings/notifications')
        ->has('notifications')
    );
});

test('returns default preferences when none exist', function () {
    $response = $this->get(route('notifications.edit'));

    $response->assertInertia(fn ($page) => $page
        ->has('notifications', 7)
        ->where('notifications.0.enabled', true)
        ->where('notifications.0.in_app', true)
    );
});

test('can update notification preferences', function () {
    $response = $this->patch(route('notifications.update'), [
        'notifications' => [
            ['type' => 'daily_limit_warning', 'in_app' => false, 'push' => false, 'enabled' => false],
            ['type' => 'bill_reminder', 'in_app' => true, 'push' => false, 'enabled' => true],
        ],
    ]);

    $response->assertRedirect(route('notifications.edit'));

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'type' => 'daily_limit_warning',
        'enabled' => false,
        'in_app' => false,
    ]);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'type' => 'bill_reminder',
        'enabled' => true,
        'in_app' => true,
    ]);
});

test('validates notification data structure', function () {
    $response = $this->patch(route('notifications.update'), [
        'notifications' => 'invalid',
    ]);

    $response->assertSessionHasErrors('notifications');
});

test('validates each notification has required fields', function () {
    $response = $this->patch(route('notifications.update'), [
        'notifications' => [
            ['type' => 'daily_limit_warning'],
        ],
    ]);

    $response->assertSessionHasErrors();
});

test('persists preferences across page loads', function () {
    NotificationPreference::factory()->for($this->user)->create([
        'type' => 'bill_reminder',
        'in_app' => false,
        'push' => true,
        'enabled' => true,
    ]);

    $response = $this->get(route('notifications.edit'));

    $response->assertInertia(fn ($page) => $page
        ->where('notifications.5.type', 'bill_reminder')
        ->where('notifications.5.in_app', false)
    );
});

test('only shows own preferences', function () {
    $otherUser = User::factory()->create();
    NotificationPreference::factory()->for($otherUser)->create([
        'type' => 'bill_reminder',
        'enabled' => false,
    ]);

    $response = $this->get(route('notifications.edit'));

    // Our user should see default (enabled=true) for bill_reminder
    $response->assertInertia(fn ($page) => $page
        ->where('notifications.5.type', 'bill_reminder')
        ->where('notifications.5.enabled', true)
    );
});
