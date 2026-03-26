<?php

use App\Models\Profile;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Profile::factory()->for($this->user)->create(['preferred_language' => 'en']);
    $this->actingAs($this->user);
});

test('can view language settings', function () {
    $response = $this->get(route('language.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/language')
        ->where('currentLanguage', 'en')
    );
});

test('can update language to tok pisin', function () {
    $response = $this->patch(route('language.update'), [
        'preferred_language' => 'tpi',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($this->user->fresh()->profile->preferred_language)->toBe('tpi');
});

test('can update language to english', function () {
    $this->user->profile->update(['preferred_language' => 'tpi']);

    $response = $this->patch(route('language.update'), [
        'preferred_language' => 'en',
    ]);

    $response->assertRedirect();
    expect($this->user->fresh()->profile->preferred_language)->toBe('en');
});

test('rejects invalid language', function () {
    $response = $this->patch(route('language.update'), [
        'preferred_language' => 'fr',
    ]);

    $response->assertSessionHasErrors('preferred_language');
    expect($this->user->fresh()->profile->preferred_language)->toBe('en');
});
