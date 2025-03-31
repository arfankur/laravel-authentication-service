<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);
// uses()->group('auth');
// uses()->group('feature');
// uses()->group('api');
// uses()->group('auth-api');
// uses()->group('auth-api-feature');
// uses()->group('auth-api-feature-auth');
// uses()->group('auth-api-feature-auth-auth');
// uses()->group('auth-api-feature-auth-auth-auth');
// uses()->group('auth-api-feature-auth-auth-auth-auth');
// uses()->group('auth-api-feature-auth-auth-auth-auth-auth');
// uses()->group('auth-api-feature-auth-auth-auth-auth-auth-auth');

test('a user can register', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(200)
             ->assertJson(['message' => 'User registered successfully']);

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

test('a user can login and receive a token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['token', 'token_type']);
});

test('an authenticated user can access protected routes', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/user');

    $response->assertStatus(200)
             ->assertJson(['email' => $user->email]);
});

test('an authenticated user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertStatus(200)
             ->assertJson(['message' => 'Logged out successfully']);
});

test('a guest cannot access protected routes', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});
