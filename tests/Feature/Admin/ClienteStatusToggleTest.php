<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\User;

it('renders the exact admin client status toggle route on the index switch', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Cliente toggle route',
        'email' => 'cliente-toggle-route@example.com',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.clientes.index'));

    $response->assertOk();
    $response->assertSee('data-toggle-url="'.route('admin.clientes.toggle-estado', $cliente).'"', false);
    $response->assertDontSee('data-toggle-url="/clientes/'.$cliente->id.'/toggle-estado"', false);
    $response->assertDontSee('fetch(`/clientes/${clienteId}/toggle-estado`', false);
});

it('toggles client status through the admin PATCH endpoint', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Cliente toggle endpoint',
        'email' => 'cliente-toggle-endpoint@example.com',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->patchJson(route('admin.clientes.toggle-estado', $cliente));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('nuevo_estado', 'inactivo');

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'estado' => 'inactivo',
    ]);
});
