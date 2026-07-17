<?php

use App\Enums\ProductoEstado;
use App\Models\Producto;
use App\Models\User;

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

it('toggles product status through the admin PATCH endpoint', function (): void {
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

    $response = $this->actingAs($admin)
        ->patchJson(route('admin.productos.toggle-estado', $producto));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('nuevo_estado', 'inactivo');

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'estado' => 'inactivo',
    ]);
});
