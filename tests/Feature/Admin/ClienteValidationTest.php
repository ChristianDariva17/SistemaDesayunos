<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\User;

function adminUser(): User
{
    return User::factory()->create([
        'rol' => 'administrador',
    ]);
}

it('normalizes cliente contact fields on create', function (): void {
    $admin = adminUser();

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.create'))
        ->post(route('admin.clientes.store'), [
            'nombre' => '  Ana  ',
            'apellido' => '   ',
            'email' => '  ANA.CLIENTE@EXAMPLE.COM  ',
            'telefono' => '   ',
            'direccion' => '   ',
            'notas' => '   ',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.clientes.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('clientes', [
        'nombre' => 'Ana',
        'apellido' => null,
        'email' => 'ana.cliente@example.com',
        'telefono' => null,
        'direccion' => null,
        'notas' => null,
        'estado' => 'activo',
    ]);
});

it('converts whitespace-only cliente email to null on create', function (): void {
    $admin = adminUser();

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.create'))
        ->post(route('admin.clientes.store'), [
            'nombre' => 'Ana',
            'email' => '   ',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.clientes.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('clientes', [
        'nombre' => 'Ana',
        'email' => null,
        'estado' => 'activo',
    ]);
});

it('allows multiple clientes with blank or null email', function (): void {
    $admin = adminUser();

    Cliente::create([
        'nombre' => 'Ana',
        'email' => null,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.create'))
        ->post(route('admin.clientes.store'), [
            'nombre' => 'Bea',
            'email' => '   ',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.clientes.index'));
    $response->assertSessionHasNoErrors();

    $this->assertSame(2, Cliente::query()
        ->whereIn('nombre', ['Ana', 'Bea'])
        ->whereNull('email')
        ->count());
});

it('rejects duplicate normalized cliente email on create', function (): void {
    $admin = adminUser();

    Cliente::create([
        'nombre' => 'Ana',
        'email' => 'ana.duplicate@example.com',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.create'))
        ->post(route('admin.clientes.store'), [
            'nombre' => 'Bea',
            'email' => '  ANA.DUPLICATE@EXAMPLE.COM  ',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.clientes.create'));
    $response->assertSessionHasErrors(['email']);

    $this->assertSame(1, Cliente::query()
        ->where('email', 'ana.duplicate@example.com')
        ->count());
    $this->assertDatabaseMissing('clientes', [
        'nombre' => 'Bea',
        'email' => 'ana.duplicate@example.com',
    ]);
});

it('normalizes cliente contact fields on update', function (): void {
    $admin = adminUser();

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.update@example.com',
        'telefono' => '+54 11 1111-1111',
        'direccion' => 'Old address',
        'notas' => 'Old notes',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.edit', $cliente))
        ->put(route('admin.clientes.update', $cliente), [
            'nombre' => '  Ana Updated  ',
            'apellido' => '   ',
            'email' => '  ANA.UPDATED@EXAMPLE.COM  ',
            'telefono' => '   ',
            'direccion' => '   ',
            'notas' => '   ',
            'estado' => 'inactivo',
        ]);

    $response->assertRedirect(route('admin.clientes.show', $cliente));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'nombre' => 'Ana Updated',
        'apellido' => null,
        'email' => 'ana.updated@example.com',
        'telefono' => null,
        'direccion' => null,
        'notas' => null,
        'estado' => 'inactivo',
    ]);
});

it('allows updating a cliente with its own normalized email', function (): void {
    $admin = adminUser();

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'email' => 'ana.self@example.com',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.edit', $cliente))
        ->put(route('admin.clientes.update', $cliente), [
            'nombre' => 'Ana',
            'email' => '  ANA.SELF@EXAMPLE.COM  ',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.clientes.show', $cliente));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'email' => 'ana.self@example.com',
    ]);
});

it('rejects updating a cliente to another cliente normalized email', function (): void {
    $admin = adminUser();

    Cliente::create([
        'nombre' => 'Ana',
        'email' => 'ana.taken@example.com',
        'estado' => 'activo',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Bea',
        'email' => 'bea.original@example.com',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.edit', $cliente))
        ->put(route('admin.clientes.update', $cliente), [
            'nombre' => 'Bea',
            'email' => '  ANA.TAKEN@EXAMPLE.COM  ',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.clientes.edit', $cliente));
    $response->assertSessionHasErrors(['email']);

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'email' => 'bea.original@example.com',
    ]);
});

it('rejects invalid cliente estado', function (): void {
    $admin = adminUser();

    $response = $this->actingAs($admin)
        ->from(route('admin.clientes.create'))
        ->post(route('admin.clientes.store'), [
            'nombre' => 'Ana',
            'email' => 'ana.estado@example.com',
            'estado' => 'suspendido',
        ]);

    $response->assertRedirect(route('admin.clientes.create'));
    $response->assertSessionHasErrors(['estado']);
});
