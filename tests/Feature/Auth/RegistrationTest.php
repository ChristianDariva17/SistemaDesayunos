<?php

use App\Models\User;

test('public registration screen can be rendered', function (): void {
    $this->get('/register')->assertOk();
});

test('authenticated users are redirected away from the registration screen', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/register')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('public users can register and are signed in as workers', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'rol' => 'trabajador',
    ]);
});
