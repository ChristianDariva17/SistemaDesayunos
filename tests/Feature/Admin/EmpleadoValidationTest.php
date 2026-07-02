<?php

declare(strict_types=1);

use App\Models\Empleado;
use App\Models\User;

function empleadoValidationAdminUser(): User
{
    return User::factory()->create([
        'rol' => 'administrador',
    ]);
}

it('normalizes empleado fields on create', function (): void {
    $admin = empleadoValidationAdminUser();

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.create'))
        ->post(route('admin.empleados.store'), [
            'user_id' => '',
            'nombre' => '  Ana Empleada  ',
            'rol_operativo' => ' mesero ',
            'telefono' => '   ',
            'observaciones' => '   ',
            'estado' => ' activo ',
        ]);

    $response->assertRedirect(route('admin.empleados.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('empleados', [
        'user_id' => null,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'telefono' => null,
        'observaciones' => null,
        'estado' => 'activo',
    ]);
});

it('rejects invalid empleado estado on create', function (): void {
    $admin = empleadoValidationAdminUser();

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.create'))
        ->post(route('admin.empleados.store'), [
            'nombre' => 'Ana Empleada',
            'rol_operativo' => 'mesero',
            'estado' => 'suspendido',
        ]);

    $response->assertRedirect(route('admin.empleados.create'));
    $response->assertSessionHasErrors(['estado']);
});

it('rejects invalid empleado rol operativo on create', function (): void {
    $admin = empleadoValidationAdminUser();

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.create'))
        ->post(route('admin.empleados.store'), [
            'nombre' => 'Ana Empleada',
            'rol_operativo' => 'supervisor',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.empleados.create'));
    $response->assertSessionHasErrors(['rol_operativo']);
});

it('allows multiple empleados with blank or null user association', function (): void {
    $admin = empleadoValidationAdminUser();

    Empleado::create([
        'user_id' => null,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.create'))
        ->post(route('admin.empleados.store'), [
            'user_id' => '',
            'nombre' => 'Bea Empleada',
            'rol_operativo' => 'cajero',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.empleados.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseCount('empleados', 2);
    $this->assertSame(2, Empleado::whereNull('user_id')->count());
});

it('rejects duplicate empleado user association on create', function (): void {
    $admin = empleadoValidationAdminUser();
    $worker = User::factory()->create();

    Empleado::create([
        'user_id' => $worker->id,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.create'))
        ->post(route('admin.empleados.store'), [
            'user_id' => $worker->id,
            'nombre' => 'Bea Empleada',
            'rol_operativo' => 'cajero',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.empleados.create'));
    $response->assertSessionHasErrors(['user_id']);

    $this->assertDatabaseCount('empleados', 1);
});

it('rejects non-existent empleado user association on create without persisting', function (): void {
    $admin = empleadoValidationAdminUser();
    $missingUserId = User::query()->max('id') + 1000;

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.create'))
        ->post(route('admin.empleados.store'), [
            'user_id' => $missingUserId,
            'nombre' => 'Ana Empleada',
            'rol_operativo' => 'mesero',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.empleados.create'));
    $response->assertSessionHasErrors(['user_id']);

    $this->assertDatabaseMissing('empleados', [
        'nombre' => 'Ana Empleada',
        'user_id' => $missingUserId,
    ]);
    $this->assertDatabaseCount('empleados', 0);
});

it('rejects non-integer empleado user association on create without persisting', function (): void {
    $admin = empleadoValidationAdminUser();

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.create'))
        ->post(route('admin.empleados.store'), [
            'user_id' => 'not-a-user-id',
            'nombre' => 'Ana Empleada',
            'rol_operativo' => 'mesero',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.empleados.create'));
    $response->assertSessionHasErrors(['user_id']);

    $this->assertDatabaseMissing('empleados', [
        'nombre' => 'Ana Empleada',
        'user_id' => 'not-a-user-id',
    ]);
    $this->assertDatabaseCount('empleados', 0);
});

it('normalizes empleado fields on update and keeps its own user association', function (): void {
    $admin = empleadoValidationAdminUser();
    $worker = User::factory()->create();

    $empleado = Empleado::create([
        'user_id' => $worker->id,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'telefono' => '999 888 777',
        'observaciones' => 'Old note',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.edit', $empleado))
        ->put(route('admin.empleados.update', $empleado), [
            'user_id' => $worker->id,
            'nombre' => '  Ana Actualizada  ',
            'rol_operativo' => ' chef ',
            'telefono' => '   ',
            'observaciones' => '   ',
            'estado' => ' inactivo ',
        ]);

    $response->assertRedirect(route('admin.empleados.show', $empleado));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('empleados', [
        'id' => $empleado->id,
        'user_id' => $worker->id,
        'nombre' => 'Ana Actualizada',
        'rol_operativo' => 'chef',
        'telefono' => null,
        'observaciones' => null,
        'estado' => 'inactivo',
    ]);
});

it('rejects updating an empleado to another empleado user association', function (): void {
    $admin = empleadoValidationAdminUser();
    $takenUser = User::factory()->create();

    Empleado::create([
        'user_id' => $takenUser->id,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'user_id' => null,
        'nombre' => 'Bea Empleada',
        'rol_operativo' => 'cajero',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.edit', $empleado))
        ->put(route('admin.empleados.update', $empleado), [
            'user_id' => $takenUser->id,
            'nombre' => 'Bea Empleada',
            'rol_operativo' => 'cajero',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.empleados.edit', $empleado));
    $response->assertSessionHasErrors(['user_id']);

    $this->assertDatabaseHas('empleados', [
        'id' => $empleado->id,
        'user_id' => null,
    ]);
});

it('rejects non-existent empleado user association on update without changing the association', function (): void {
    $admin = empleadoValidationAdminUser();
    $worker = User::factory()->create();
    $missingUserId = User::query()->max('id') + 1000;

    $empleado = Empleado::create([
        'user_id' => $worker->id,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.edit', $empleado))
        ->put(route('admin.empleados.update', $empleado), [
            'user_id' => $missingUserId,
            'nombre' => 'Ana Actualizada',
            'rol_operativo' => 'chef',
            'estado' => 'inactivo',
        ]);

    $response->assertRedirect(route('admin.empleados.edit', $empleado));
    $response->assertSessionHasErrors(['user_id']);

    $this->assertDatabaseHas('empleados', [
        'id' => $empleado->id,
        'user_id' => $worker->id,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
});

it('rejects non-integer empleado user association on update without changing the association', function (): void {
    $admin = empleadoValidationAdminUser();
    $worker = User::factory()->create();

    $empleado = Empleado::create([
        'user_id' => $worker->id,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.empleados.edit', $empleado))
        ->put(route('admin.empleados.update', $empleado), [
            'user_id' => 'not-a-user-id',
            'nombre' => 'Ana Actualizada',
            'rol_operativo' => 'chef',
            'estado' => 'inactivo',
        ]);

    $response->assertRedirect(route('admin.empleados.edit', $empleado));
    $response->assertSessionHasErrors(['user_id']);

    $this->assertDatabaseHas('empleados', [
        'id' => $empleado->id,
        'user_id' => $worker->id,
        'nombre' => 'Ana Empleada',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
});
