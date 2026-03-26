<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays the language settings page', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'preferred_language' => 'en']);

    $this->actingAs($user)
        ->get('/settings/language')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('settings/language')
            ->where('currentLanguage', 'en')
        );
});

it('updates language preference to tok pisin', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id, 'preferred_language' => 'en']);

    $this->actingAs($user)
        ->patch('/settings/language', ['preferred_language' => 'tpi'])
        ->assertRedirect();

    expect($profile->fresh()->preferred_language)->toBe('tpi');
});

it('updates language preference to english', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id, 'preferred_language' => 'tpi']);

    $this->actingAs($user)
        ->patch('/settings/language', ['preferred_language' => 'en'])
        ->assertRedirect();

    expect($profile->fresh()->preferred_language)->toBe('en');
});

it('rejects invalid language values', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'preferred_language' => 'en']);

    $this->actingAs($user)
        ->patch('/settings/language', ['preferred_language' => 'fr'])
        ->assertSessionHasErrors('preferred_language');
});

it('requires preferred_language field', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patch('/settings/language', [])
        ->assertSessionHasErrors('preferred_language');
});

it('requires authentication to access language settings', function () {
    $this->get('/settings/language')
        ->assertRedirect('/login');
});

it('persists language preference in the database', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'preferred_language' => 'en']);

    $this->actingAs($user)
        ->patch('/settings/language', ['preferred_language' => 'tpi']);

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'preferred_language' => 'tpi',
    ]);
});
