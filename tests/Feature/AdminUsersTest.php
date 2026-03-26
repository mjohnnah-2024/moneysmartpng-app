<?php

use App\Models\Budget;
use App\Models\ChatMessage;
use App\Models\Goal;
use App\Models\ManualPayment;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    Profile::factory()->for($this->admin)->create();
});

test('admin can access users index', function () {
    User::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/users'));
});

test('non-admin cannot access users index', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('users index supports search', function () {
    User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['search' => 'Alice']));

    $response->assertOk();
    $users = $response->getOriginalContent()->getData()['page']['props']['users']['data'];
    expect($users)->toHaveCount(1);
    expect($users[0]['name'])->toBe('Alice Smith');
});

test('users index supports sorting', function () {
    User::factory()->create(['name' => 'Zara']);
    User::factory()->create(['name' => 'Alice']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['sort' => 'name', 'direction' => 'asc']));

    $response->assertOk();
    $users = $response->getOriginalContent()->getData()['page']['props']['users']['data'];
    expect($users[0]['name'])->toBe('Alice');
});

test('admin can view user detail', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.users.show', $user))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/user-detail'));
});

test('admin can grant premium to a user', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create(['plan' => 'free']);

    $this->actingAs($this->admin)
        ->post(route('admin.users.grant-premium', $user), ['months' => 3])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->profile->plan)->toBe('premium');
    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'plan' => 'premium',
        'status' => 'active',
    ]);
});

test('granting premium expires previous active subscription', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create(['plan' => 'premium']);
    $old = Subscription::factory()->active()->for($user)->create();

    $this->actingAs($this->admin)
        ->post(route('admin.users.grant-premium', $user), ['months' => 1]);

    expect($old->fresh()->status)->toBe('expired');
    expect(Subscription::where('user_id', $user->id)->where('status', 'active')->count())->toBe(1);
});

test('grant premium requires valid months', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.users.grant-premium', $user), ['months' => 5])
        ->assertSessionHasErrors('months');
});

test('admin can revoke premium', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create(['plan' => 'premium']);
    Subscription::factory()->active()->for($user)->create();

    $this->actingAs($this->admin)
        ->post(route('admin.users.revoke-premium', $user))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->profile->plan)->toBe('free');
    $this->assertDatabaseMissing('subscriptions', [
        'user_id' => $user->id,
        'status' => 'active',
    ]);
});

test('admin can delete a non-admin user', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();
    Transaction::factory()->for($user)->create();
    ChatMessage::factory()->for($user)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertDatabaseMissing('profiles', ['user_id' => $user->id]);
    $this->assertDatabaseMissing('transactions', ['user_id' => $user->id]);
    $this->assertDatabaseMissing('chat_messages', ['user_id' => $user->id]);
});

test('admin cannot delete another admin', function () {
    $otherAdmin = User::factory()->admin()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $otherAdmin))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
});
