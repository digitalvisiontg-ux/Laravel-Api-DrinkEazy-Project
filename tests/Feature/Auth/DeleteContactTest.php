<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows deleting one contact when both exist', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'phone' => '1234567890',
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/auth/delete-contact', ['type' => 'phone']);
    $response->assertStatus(200)->assertJson(['success' => true]);

    expect($user->fresh()->phone)->toBeNull();
    expect($user->fresh()->email)->toBe('test@example.com');

    // now try removing the remaining email should fail because only one left
    $response2 = $this->postJson('/api/auth/delete-contact', ['type' => 'email']);
    $response2->assertStatus(422)->assertJson(['success' => false]);
});

it('refuses to delete the only email', function () {
    $user = User::factory()->create(['email' => 'solo@example.com', 'phone' => null]);
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/auth/delete-contact', ['type' => 'email']);
    $response->assertStatus(422)->assertJson(['success' => false]);
});

it('refuses to delete the only phone', function () {
    $user = User::factory()->create(['email' => null, 'phone' => '0987654321']);
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/auth/delete-contact', ['type' => 'phone']);
    $response->assertStatus(422)->assertJson(['success' => false]);
});

it('returns validation error when type is invalid', function () {
    $user = User::factory()->create(['email' => 'a@b.com', 'phone' => '123']);
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/auth/delete-contact', ['type' => 'foobar']);
    $response->assertStatus(422);
});
