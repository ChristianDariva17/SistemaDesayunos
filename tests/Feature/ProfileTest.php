<?php

use App\Models\User;

test('profile controls are rendered for each staff role', function (string $role, string $layoutText) {
    $user = User::factory()->create(['rol' => $role]);

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response
        ->assertOk()
        ->assertSee($layoutText)
        ->assertSee('Profile Information')
        ->assertSee('action="'.route('profile.update').'"', false)
        ->assertSee('Update Password')
        ->assertSee('action="'.route('password.update').'"', false)
        ->assertSee('Delete Account')
        ->assertSee('action="'.route('profile.destroy').'"', false);
})->with([
    'administrator' => ['administrador', 'Menú Principal'],
    'worker' => ['trabajador', 'Panel Trabajador'],
]);

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
