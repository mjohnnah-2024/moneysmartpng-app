<?php

use App\Models\ChatMessage;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

test('admin cannot delete self', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
});

// --- Create User Tests ---

test('admin can access create user form', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/user-form'));
});

test('admin can create a user', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'is_admin' => false,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
        'name' => 'New User',
        'is_admin' => false,
    ]);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
});

test('admin can create an admin user', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Admin User',
            'email' => 'adminuser@example.com',
            'password' => 'password123',
            'is_admin' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'adminuser@example.com',
        'is_admin' => true,
    ]);
});

test('create user validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

test('create user validates unique email', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ])
        ->assertSessionHasErrors('email');
});

test('create user validates password minimum length', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Short Pass',
            'email' => 'short@example.com',
            'password' => 'short',
        ])
        ->assertSessionHasErrors('password');
});

// --- Edit / Update User Tests ---

test('admin can access edit user form', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.users.edit', $user))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/user-form')
            ->has('user')
            ->where('user.id', $user->id));
});

test('admin can update a user', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'is_admin' => false,
        ])
        ->assertRedirect(route('admin.users.show', $user))
        ->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

test('admin can grant admin via update', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => true,
        ])
        ->assertRedirect();

    expect($user->fresh()->is_admin)->toBeTrue();
});

test('update user validates unique email ignoring self', function () {
    $user = User::factory()->create(['email' => 'keep@example.com']);
    $other = User::factory()->create(['email' => 'other@example.com']);

    // Keeping own email should pass
    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => 'keep@example.com',
        ])
        ->assertSessionDoesntHaveErrors('email');

    // Using another user's email should fail
    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => 'other@example.com',
        ])
        ->assertSessionHasErrors('email');
});

// --- Toggle Admin Tests ---

test('admin can toggle admin status on', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($this->admin)
        ->post(route('admin.users.toggle-admin', $user))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->is_admin)->toBeTrue();
});

test('admin can toggle admin status off', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.users.toggle-admin', $user))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->is_admin)->toBeFalse();
});

test('admin cannot toggle own admin status', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.toggle-admin', $this->admin))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($this->admin->fresh()->is_admin)->toBeTrue();
});

// --- Non-admin Access Tests ---

test('non-admin cannot access create form', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('admin.users.create'))
        ->assertForbidden();
});

test('non-admin cannot store a user', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password123',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com']);
});

test('non-admin cannot update a user', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();
    $target = User::factory()->create(['name' => 'Original']);

    $this->actingAs($user)
        ->put(route('admin.users.update', $target), [
            'name' => 'Hacked',
            'email' => $target->email,
        ])
        ->assertForbidden();

    expect($target->fresh()->name)->toBe('Original');
});

test('non-admin cannot toggle admin', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();
    $target = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post(route('admin.users.toggle-admin', $target))
        ->assertForbidden();

    expect($target->fresh()->is_admin)->toBeFalse();
});
