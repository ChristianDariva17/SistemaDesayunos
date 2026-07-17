<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

it('toggles an active client through the admin PATCH endpoint with the exact contract and log', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Cliente toggle endpoint',
        'email' => 'cliente-toggle-endpoint@example.com',
        'estado' => 'activo',
    ]);

    Log::shouldReceive('info')
        ->once()
        ->with('Estado de cliente cambiado', [
            'cliente_id' => $cliente->id,
            'nombre_completo' => 'Cliente toggle endpoint',
            'estado_anterior' => 'activo',
            'estado_nuevo' => 'inactivo',
            'usuario' => $admin->id,
        ]);

    $response = $this->actingAs($admin)
        ->patchJson(route('admin.clientes.toggle-estado', $cliente));

    $response->assertOk()
        ->assertExactJson([
            'success' => true,
            'message' => 'Estado cambiado a: Inactivo',
            'nuevo_estado' => 'inactivo',
        ]);

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'estado' => 'inactivo',
    ]);
});

it('toggles an inactive client back to active', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $cliente = Cliente::create([
        'nombre' => 'Cliente inactive toggle',
        'email' => 'cliente-inactive-toggle@example.com',
        'estado' => 'inactivo',
    ]);

    $this->actingAs($admin)
        ->patchJson(route('admin.clientes.toggle-estado', $cliente))
        ->assertOk()
        ->assertExactJson([
            'success' => true,
            'message' => 'Estado cambiado a: Activo',
            'nuevo_estado' => 'activo',
        ]);

    expect($cliente->refresh()->estado)->toBe('activo');
});

it('toggles every malformed persisted client status to active', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $cliente = Cliente::create([
        'nombre' => 'Cliente malformed state',
        'email' => 'cliente-malformed-state@example.com',
        'estado' => 'activo',
    ]);
    DB::table('clientes')->where('id', $cliente->id)->update(['estado' => 'desconocido']);

    $this->actingAs($admin)
        ->patchJson(route('admin.clientes.toggle-estado', $cliente))
        ->assertOk()
        ->assertExactJson([
            'success' => true,
            'message' => 'Estado cambiado a: Activo',
            'nuevo_estado' => 'activo',
        ]);

    expect($cliente->refresh()->estado)->toBe('activo');
});

it('denies client status toggles to workers without mutating the client', function (): void {
    $worker = User::factory()->create(['rol' => 'trabajador']);
    $cliente = Cliente::create([
        'nombre' => 'Cliente denied toggle',
        'email' => 'cliente-denied-toggle@example.com',
        'estado' => 'activo',
    ]);

    $this->actingAs($worker)
        ->patchJson(route('admin.clientes.toggle-estado', $cliente))
        ->assertForbidden();

    expect($cliente->refresh()->estado)->toBe('activo');
});

it('returns the existing client error contract when the action update fails', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $cliente = Cliente::create([
        'nombre' => 'Cliente failed toggle',
        'email' => 'cliente-failed-toggle@example.com',
        'estado' => 'activo',
    ]);

    Cliente::updating(static function (Cliente $updating) use ($cliente): void {
        if ($updating->is($cliente)) {
            throw new RuntimeException('Simulated client status update failure.');
        }
    });

    Log::shouldReceive('error')
        ->once()
        ->with('Error al cambiar estado de cliente', [
            'cliente_id' => $cliente->id,
            'error' => 'Simulated client status update failure.',
        ]);

    $this->actingAs($admin)
        ->patchJson(route('admin.clientes.toggle-estado', $cliente))
        ->assertStatus(500)
        ->assertExactJson([
            'success' => false,
            'message' => 'Error al cambiar el estado',
        ]);

    expect($cliente->refresh()->estado)->toBe('activo');
});
