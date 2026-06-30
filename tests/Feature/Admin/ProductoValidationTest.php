<?php

use App\Models\Producto;
use App\Models\User;

it('validates producto creation through the admin form request', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'descripcion' => 'Producto sin nombre',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => -1,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.create'));
    $response->assertSessionHasErrors(['nombre', 'stock']);
});

it('allows updating a producto without failing its own unique fields', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich',
        'descripcion' => 'Original',
        'categoria' => 'desayuno',
        'precio' => 15.00,
        'codigo_barras' => '1234567890',
        'sku' => 'SKU-001',
        'stock' => 12,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Sandwich Especial',
            'descripcion' => 'Actualizado',
            'categoria' => 'desayuno',
            'precio' => 17.50,
            'codigo_barras' => '1234567890',
            'sku' => 'SKU-001',
            'stock' => 9,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Sandwich Especial',
        'stock' => 9,
        'codigo_barras' => '1234567890',
        'sku' => 'SKU-001',
    ]);
});
