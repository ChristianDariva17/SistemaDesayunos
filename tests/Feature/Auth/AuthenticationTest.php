<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('login form submits to the POST login route contract', function () {
    $view = File::get(resource_path('views/auth/login.blade.php'));

    expect($view)->toContain("route('login.post')");
});

test('authenticated users are redirected away from the login screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/login')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users with an invalid role are logged out immediately after login and cannot access auth-only routes', function (): void {
    $connection = DB::connection();

    if ($connection->getDriverName() === 'sqlite') {
        $connection->statement('PRAGMA ignore_check_constraints = ON');
    }

    try {
        $user = User::factory()->create();
        DB::table('users')->where('id', $user->id)->update([
            'rol' => 'supervisor',
        ]);
        $user->refresh();
    } finally {
        if ($connection->getDriverName() === 'sqlite') {
            $connection->statement('PRAGMA ignore_check_constraints = OFF');
        }
    }

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();

    $this->get('/profile')->assertRedirect(route('login', absolute: false));
});

test('unsupported-role sessions are rejected on generic auth-only routes', function (): void {
    $connection = DB::connection();

    if ($connection->getDriverName() === 'sqlite') {
        $connection->statement('PRAGMA ignore_check_constraints = ON');
    }

    try {
        $user = User::factory()->create();
        DB::table('users')->where('id', $user->id)->update([
            'rol' => 'supervisor',
        ]);
        $user->refresh();
    } finally {
        if ($connection->getDriverName() === 'sqlite') {
            $connection->statement('PRAGMA ignore_check_constraints = OFF');
        }
    }

    $this->actingAs($user)
        ->get('/profile')
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();
});

test('administrator users are still routed through the shared dashboard landing page', function () {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('dashboard redirects administrators to the admin dashboard', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('admin.dashboard', absolute: false));
});

test('dashboard redirects workers to the worker dashboard', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('trabajador.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));
});
