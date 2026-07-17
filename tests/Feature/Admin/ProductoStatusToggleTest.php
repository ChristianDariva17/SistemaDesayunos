<?php

declare(strict_types=1);

use App\Enums\ProductoEstado;
use App\Models\Audit;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

it('defines canonical product states and keeps the model attribute as a string', function (): void {
    $producto = Producto::create([
        'nombre' => 'Producto enum state',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => ProductoEstado::Active->value,
    ])->refresh();

    expect(ProductoEstado::values())->toBe(['activo', 'inactivo'])
        ->and(ProductoEstado::Active->label())->toBe('Activo')
        ->and(ProductoEstado::Inactive->label())->toBe('Inactivo')
        ->and(ProductoEstado::Active->toggled())->toBe(ProductoEstado::Inactive)
        ->and(ProductoEstado::Inactive->toggled())->toBe(ProductoEstado::Active)
        ->and($producto->estado)->toBeString()->toBe('activo')
        ->and($producto->isActive())->toBeTrue();
});

it('renders the exact admin product status toggle route on the index switch', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto toggle route',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.productos.index'));

    $response->assertOk();
    $response->assertSee('data-toggle-url="'.route('admin.productos.toggle-estado', $producto).'"', false);
    $response->assertDontSee('data-toggle-url="/productos/'.$producto->id.'/toggle-estado"', false);
});

it('toggles an active product through the admin PATCH endpoint with the exact contract, log, and audit', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto toggle endpoint',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    Log::shouldReceive('info')
        ->once()
        ->with('Estado de producto cambiado', [
            'producto_id' => $producto->id,
            'estado_anterior' => 'activo',
            'estado_nuevo' => 'inactivo',
        ]);

    $response = $this->actingAs($admin)
        ->patchJson(route('admin.productos.toggle-estado', $producto));

    $response->assertOk()
        ->assertExactJson([
            'success' => true,
            'message' => 'Estado cambiado a: Inactivo',
            'nuevo_estado' => 'inactivo',
        ]);

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'estado' => 'inactivo',
    ]);

    $audit = Audit::query()
        ->where('auditable_type', Producto::class)
        ->where('auditable_id', $producto->id)
        ->where('action', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($audit->user_id)->toBe($admin->id)
        ->and($audit->old_values)->toMatchArray(['estado' => 'activo'])
        ->and($audit->new_values)->toMatchArray(['estado' => 'inactivo']);
});

it('toggles an inactive product back to active', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $producto = Producto::create([
        'nombre' => 'Producto inactive toggle',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'inactivo',
    ]);

    $this->actingAs($admin)
        ->patchJson(route('admin.productos.toggle-estado', $producto))
        ->assertOk()
        ->assertExactJson([
            'success' => true,
            'message' => 'Estado cambiado a: Activo',
            'nuevo_estado' => 'activo',
        ]);

    expect($producto->refresh()->estado)->toBe('activo');
});

it('treats a malformed persisted product status as inactive and toggles it to active', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $producto = Producto::create([
        'nombre' => 'Producto malformed state',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);
    DB::table('productos')->where('id', $producto->id)->update(['estado' => 'desconocido']);

    $this->actingAs($admin)
        ->patchJson(route('admin.productos.toggle-estado', $producto))
        ->assertOk()
        ->assertExactJson([
            'success' => true,
            'message' => 'Estado cambiado a: Activo',
            'nuevo_estado' => 'activo',
        ]);

    expect($producto->refresh()->estado)->toBe('activo');
});

it('denies product status toggles to workers without mutating the product', function (): void {
    $worker = User::factory()->create(['rol' => 'trabajador']);
    $producto = Producto::create([
        'nombre' => 'Producto denied toggle',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $this->actingAs($worker)
        ->patchJson(route('admin.productos.toggle-estado', $producto))
        ->assertForbidden();

    expect($producto->refresh()->estado)->toBe('activo');
});

it('returns the existing product error contract when the action update fails', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $producto = Producto::create([
        'nombre' => 'Producto failed toggle',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    Producto::updating(static function (Producto $updating) use ($producto): void {
        if ($updating->is($producto)) {
            throw new RuntimeException('Simulated product status update failure.');
        }
    });

    Log::shouldReceive('error')
        ->once()
        ->with('Error al cambiar estado de producto', [
            'producto_id' => $producto->id,
            'error' => 'Simulated product status update failure.',
        ]);

    $this->actingAs($admin)
        ->patchJson(route('admin.productos.toggle-estado', $producto))
        ->assertStatus(500)
        ->assertExactJson([
            'success' => false,
            'message' => 'Error al cambiar el estado',
        ]);

    expect($producto->refresh()->estado)->toBe('activo');
});
