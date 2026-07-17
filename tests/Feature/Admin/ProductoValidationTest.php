<?php

use App\Http\Controllers\Admin\ProductoController;
use App\Http\Requests\Admin\UpdateProductoRequest;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

it('rejects an invalid producto estado through enum validation', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto invalid state',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'suspendido',
        ])
        ->assertRedirect(route('admin.productos.create'))
        ->assertSessionHasErrors([
            'estado' => 'El estado debe ser activo o inactivo.',
        ]);

    $this->assertDatabaseMissing('productos', [
        'nombre' => 'Producto invalid state',
    ]);
});

it('treats stock 10 as low stock and standardizes the inventory scope', function (): void {
    $lowStock = Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    $normalStock = Producto::create([
        'nombre' => 'Jugo',
        'categoria' => 'bebida',
        'stock' => 11,
        'estado' => 'activo',
        'precio' => 7.50,
    ]);

    expect($lowStock->tiene_stock_bajo)->toBeTrue()
        ->and($normalStock->tiene_stock_bajo)->toBeFalse()
        ->and(Producto::stockBajo()->count())->toBe(1);

    expect(Producto::stockBajo()->pluck('id')->all())->toContain($lowStock->id)
        ->and(Producto::stockBajo()->pluck('id')->all())->not->toContain($normalStock->id);
});

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

it('rejects blank producto nombre after trimming whitespace on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => "\t  \n",
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.create'));
    $response->assertSessionHasErrors(['nombre']);

    $this->assertDatabaseMissing('productos', [
        'descripcion' => 'Producto de prueba',
    ]);
});

it('stores trimmed producto nombre on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => '  Producto nombre normalizado  ',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto nombre normalizado',
    ]);
});

it('stores blank producto descripcion as null on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto descripcion en blanco',
            'descripcion' => "\t  \n",
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto descripcion en blanco',
        'descripcion' => null,
    ]);
});

it('stores unicode blank producto descripcion as null on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto descripcion unicode en blanco',
            'descripcion' => "\u{00A0}\u{2003}",
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto descripcion unicode en blanco',
        'descripcion' => null,
    ]);
});

it('stores trimmed producto descripcion on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto descripcion normalizada',
            'descripcion' => '  Descripcion de prueba  ',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto descripcion normalizada',
        'descripcion' => 'Descripcion de prueba',
    ]);
});

it('trims unicode whitespace around producto descripcion on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto descripcion unicode normalizada',
            'descripcion' => "\u{00A0}Descripcion de prueba\u{2003}",
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto descripcion unicode normalizada',
        'descripcion' => 'Descripcion de prueba',
    ]);
});

it('requires categoria when creating a producto', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto sin categoria',
            'descripcion' => 'Producto con categoria faltante',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.create'));
    $response->assertSessionHasErrors(['categoria']);
});

it('rejects blank producto categoria after trimming whitespace on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto con categoria en blanco',
            'descripcion' => 'Producto de prueba',
            'categoria' => '   ',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.create'));
    $response->assertSessionHasErrors(['categoria']);

    $this->assertDatabaseMissing('productos', [
        'nombre' => 'Producto con categoria en blanco',
    ]);
});

it('stores trimmed producto categoria on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto categoria normalizada',
            'descripcion' => 'Producto de prueba',
            'categoria' => '  bebida  ',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto categoria normalizada',
        'categoria' => 'bebida',
    ]);
});

it('rejects blank producto categoria after trimming whitespace on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto categoria editable',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto categoria editable',
            'descripcion' => 'Producto de prueba',
            'categoria' => "\t  \n",
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.edit', $producto));
    $response->assertSessionHasErrors(['categoria']);

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'categoria' => 'bebida',
    ]);
});

it('stores trimmed producto categoria on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto categoria para normalizar',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto categoria para normalizar',
            'descripcion' => 'Producto de prueba actualizado',
            'categoria' => '  panaderia  ',
            'precio' => 13.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'categoria' => 'panaderia',
    ]);
});

it('rejects blank producto nombre after trimming whitespace on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto nombre editable',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => '   ',
            'descripcion' => 'Producto de prueba actualizado',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.edit', $producto));
    $response->assertSessionHasErrors(['nombre']);

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Producto nombre editable',
    ]);
});

it('stores trimmed producto nombre on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto nombre para normalizar',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => '  Producto nombre actualizado  ',
            'descripcion' => 'Producto de prueba actualizado',
            'categoria' => 'bebida',
            'precio' => 13.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Producto nombre actualizado',
    ]);
});

it('stores blank producto descripcion as null on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto descripcion editable',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto descripcion editable',
            'descripcion' => '   ',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'descripcion' => null,
    ]);
});

it('stores unicode blank producto descripcion as null on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto descripcion unicode editable',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto descripcion unicode editable',
            'descripcion' => "\u{00A0}\u{2003}",
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'descripcion' => null,
    ]);
});

it('stores trimmed producto descripcion on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto descripcion para normalizar',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto descripcion para normalizar',
            'descripcion' => '  Descripcion actualizada  ',
            'categoria' => 'bebida',
            'precio' => 13.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'descripcion' => 'Descripcion actualizada',
    ]);
});

it('trims unicode whitespace around producto descripcion on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto descripcion unicode para normalizar',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto descripcion unicode para normalizar',
            'descripcion' => "\u{00A0}Descripcion actualizada\u{2003}",
            'categoria' => 'bebida',
            'precio' => 13.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'descripcion' => 'Descripcion actualizada',
    ]);
});

it('allows an admin to create and update producto minimum stock', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto con mínimo',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'stock_minimo' => 3,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $producto = Producto::where('nombre', 'Producto con mínimo')->firstOrFail();

    expect($producto->stock_minimo)->toBe(3);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto con mínimo actualizado',
            'descripcion' => 'Producto actualizado',
            'categoria' => 'bebida',
            'precio' => 13.50,
            'stock' => 5,
            'stock_minimo' => 7,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Producto con mínimo actualizado',
        'stock_minimo' => 7,
    ]);
});

it('defaults blank producto minimum stock to zero on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto sin mínimo explícito',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'stock_minimo' => '',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto sin mínimo explícito',
        'stock_minimo' => 0,
    ]);
});

it('defaults omitted producto minimum stock to zero on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto mínimo omitido',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto mínimo omitido',
        'stock_minimo' => 0,
    ]);
});

it('defaults blank producto minimum stock to zero on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto mínimo editable',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'stock_minimo' => 4,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto mínimo editable',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'stock_minimo' => '',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'stock_minimo' => 0,
    ]);
});

it('stores blank producto identifiers as null on create', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto sin identificadores',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'codigo_barras' => '   ',
            'sku' => '',
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'nombre' => 'Producto sin identificadores',
        'codigo_barras' => null,
        'sku' => null,
        'stock_minimo' => 0,
    ]);
});

it('stores blank producto identifiers as null on update', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto identificadores editables',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'codigo_barras' => 'BAR-EDITABLE',
        'sku' => 'SKU-EDITABLE',
        'stock' => 5,
        'stock_minimo' => 4,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto identificadores editables',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'codigo_barras' => '   ',
            'sku' => "\t",
            'stock' => 5,
            'stock_minimo' => '',
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'codigo_barras' => null,
        'sku' => null,
        'stock_minimo' => 0,
    ]);
});

it('rejects duplicate producto barcode after trimming whitespace', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Producto con código',
        'descripcion' => 'Producto existente',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'codigo_barras' => 'BAR-001',
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto código duplicado',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'codigo_barras' => '  BAR-001  ',
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.create'));
    $response->assertSessionHasErrors(['codigo_barras']);

    $this->assertDatabaseMissing('productos', [
        'nombre' => 'Producto código duplicado',
    ]);
});

it('rejects duplicate producto sku after trimming whitespace', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Producto con SKU',
        'descripcion' => 'Producto existente',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'sku' => 'SKU-001',
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto SKU duplicado',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'sku' => "\nSKU-001\t",
            'stock' => 5,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.create'));
    $response->assertSessionHasErrors(['sku']);

    $this->assertDatabaseMissing('productos', [
        'nombre' => 'Producto SKU duplicado',
    ]);
});

it('rejects duplicate producto barcode on update after trimming whitespace', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Producto con código existente',
        'descripcion' => 'Producto existente',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'codigo_barras' => 'BAR-UPDATE-001',
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto código editable',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'codigo_barras' => 'BAR-UPDATE-002',
        'stock' => 4,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto código editable',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 10.00,
            'codigo_barras' => '  BAR-UPDATE-001  ',
            'stock' => 4,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.edit', $producto));
    $response->assertSessionHasErrors(['codigo_barras']);

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'codigo_barras' => 'BAR-UPDATE-002',
    ]);
});

it('rejects duplicate producto sku on update after trimming whitespace', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Producto con SKU existente',
        'descripcion' => 'Producto existente',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'sku' => 'SKU-UPDATE-001',
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Producto SKU editable',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'sku' => 'SKU-UPDATE-002',
        'stock' => 4,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Producto SKU editable',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 10.00,
            'sku' => "\nSKU-UPDATE-001\t",
            'stock' => 4,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.edit', $producto));
    $response->assertSessionHasErrors(['sku']);

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'sku' => 'SKU-UPDATE-002',
    ]);
});

it('validates producto minimum stock boundaries', function (mixed $minimum): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.create'))
        ->post(route('admin.productos.store'), [
            'nombre' => 'Producto mínimo inválido',
            'descripcion' => 'Producto de prueba',
            'categoria' => 'bebida',
            'precio' => 12.50,
            'stock' => 5,
            'stock_minimo' => $minimum,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.create'));
    $response->assertSessionHasErrors(['stock_minimo']);
})->with([
    'negative' => -1,
    'decimal' => 1.5,
    'overflow' => 1000000,
]);

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
            'codigo_barras' => '  1234567890  ',
            'sku' => "\tSKU-001\n",
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

it('records an ajuste stock movement when an admin product edit changes stock', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Pan con pollo',
        'descripcion' => 'Original',
        'categoria' => 'desayuno',
        'precio' => 10.00,
        'stock' => 8,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Pan con pollo',
            'descripcion' => 'Original',
            'categoria' => 'desayuno',
            'precio' => 10.00,
            'stock' => 13,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'stock' => 13,
    ]);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'user_id' => $admin->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 5,
        'stock_anterior' => 8,
        'stock_nuevo' => 13,
        'motivo' => 'Manual product edit stock adjustment',
    ]);
});

it('records admin product edit stock movement from the locked database stock when route model state is stale', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe con leche',
        'descripcion' => 'Original',
        'categoria' => 'bebida',
        'precio' => 8.00,
        'stock' => 8,
        'estado' => 'activo',
    ]);

    $staleProducto = $producto->fresh();

    Producto::query()
        ->whereKey($producto->id)
        ->update([
            'stock' => 3,
        ]);

    $payload = [
        'nombre' => 'Cafe con leche premium',
        'descripcion' => 'Updated',
        'categoria' => 'bebida',
        'precio' => 9.00,
        'stock' => 6,
        'estado' => 'activo',
    ];

    $request = UpdateProductoRequest::create(route('admin.productos.update', $producto), 'PUT', $payload);
    $request->headers->set('referer', route('admin.productos.edit', $producto));
    $request->setLaravelSession($this->app['session.store']);
    $request->setUserResolver(fn (): User => $admin);

    $request->setRouteResolver(fn (): object => new class($staleProducto)
    {
        public function __construct(private readonly Producto $producto) {}

        public function parameter(string $name, mixed $default = null): mixed
        {
            return $name === 'producto' ? $this->producto : $default;
        }
    });
    $request->setValidator(Validator::make($payload, $request->rules()));

    $response = app(ProductoController::class)->update($request, $staleProducto);

    expect($response->getTargetUrl())->toBe(route('admin.productos.show', $producto));

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Cafe con leche premium',
        'stock' => 6,
    ]);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'user_id' => $admin->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 3,
        'stock_anterior' => 3,
        'stock_nuevo' => 6,
        'motivo' => 'Manual product edit stock adjustment',
    ]);

    $this->assertDatabaseMissing('stock_movimientos', [
        'producto_id' => $producto->id,
        'stock_anterior' => 8,
        'stock_nuevo' => 6,
    ]);
});

it('does not record an ajuste stock movement when an admin product edit keeps stock unchanged', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Jugo natural',
        'descripcion' => 'Original',
        'categoria' => 'bebida',
        'precio' => 7.50,
        'stock' => 6,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.edit', $producto))
        ->put(route('admin.productos.update', $producto), [
            'nombre' => 'Jugo natural premium',
            'descripcion' => 'Updated',
            'categoria' => 'bebida',
            'precio' => 8.00,
            'stock' => 6,
            'estado' => 'activo',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre' => 'Jugo natural premium',
        'stock' => 6,
    ]);

    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('records an ajuste stock movement when the manual stock endpoint changes stock', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Ensalada de frutas',
        'descripcion' => 'Original',
        'categoria' => 'desayuno',
        'precio' => 9.50,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.show', $producto))
        ->patch(route('admin.productos.actualizar-stock', $producto), [
            'tipo' => 'decrementar',
            'cantidad' => 4,
            'motivo' => 'Inventory count correction',
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'stock' => 6,
    ]);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'user_id' => $admin->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 4,
        'stock_anterior' => 10,
        'stock_nuevo' => 6,
        'motivo' => 'Inventory count correction',
    ]);
});

it('normalizes blank manual stock motivo values to null', function (string $motivo): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Pan integral',
        'descripcion' => 'Original',
        'categoria' => 'desayuno',
        'precio' => 6.50,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.show', $producto))
        ->patch(route('admin.productos.actualizar-stock', $producto), [
            'tipo' => 'incrementar',
            'cantidad' => 1,
            'motivo' => $motivo,
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'stock_anterior' => 10,
        'stock_nuevo' => 11,
        'motivo' => null,
    ]);
})->with([
    'ASCII blank' => [" \t\n "],
    'Unicode and NBSP blank' => ["\u{FEFF}\u{00A0}\u{2003}"],
]);

it('stores omitted manual stock motivo as null', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Medialunas',
        'descripcion' => 'Original',
        'categoria' => 'desayuno',
        'precio' => 8.50,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.show', $producto))
        ->patch(route('admin.productos.actualizar-stock', $producto), [
            'tipo' => 'incrementar',
            'cantidad' => 1,
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'stock_anterior' => 10,
        'stock_nuevo' => 11,
        'motivo' => null,
    ]);
});

it('trims manual stock motivo values before persistence', function (string $motivo, string $expectedMotivo): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Tostadas',
        'descripcion' => 'Original',
        'categoria' => 'desayuno',
        'precio' => 7.25,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.show', $producto))
        ->patch(route('admin.productos.actualizar-stock', $producto), [
            'tipo' => 'incrementar',
            'cantidad' => 1,
            'motivo' => $motivo,
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'stock_anterior' => 10,
        'stock_nuevo' => 11,
        'motivo' => $expectedMotivo,
    ]);
})->with([
    'normal trimmed value' => ['  Cycle count correction  ', 'Cycle count correction'],
    'Unicode-edge trimmed value' => ["\u{FEFF}\u{00A0}Inventory count correction\u{2003}\u{00A0}", 'Inventory count correction'],
]);

it('validates manual stock motivo max length after trimming unicode whitespace', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Cafe molido',
        'descripcion' => 'Original',
        'categoria' => 'bebida',
        'precio' => 12.00,
        'stock' => 10,
        'estado' => 'activo',
    ]);
    $motivo = str_repeat('x', 255);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.show', $producto))
        ->patch(route('admin.productos.actualizar-stock', $producto), [
            'tipo' => 'incrementar',
            'cantidad' => 1,
            'motivo' => "\u{00A0}".$motivo."\u{2003}",
        ]);

    $response->assertRedirect(route('admin.productos.show', $producto));
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'stock_anterior' => 10,
        'stock_nuevo' => 11,
        'motivo' => $motivo,
    ]);
});

it('validates manual stock decrements against the reloaded product stock', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Yogurt con granola',
        'descripcion' => 'Original',
        'categoria' => 'desayuno',
        'precio' => 11.00,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    $staleProducto = $producto->fresh();

    Producto::query()
        ->whereKey($producto->id)
        ->update([
            'stock' => 3,
        ]);

    $request = Request::create(route('admin.productos.actualizar-stock', $producto), 'PATCH', [
        'tipo' => 'decrementar',
        'cantidad' => 4,
        'motivo' => 'Inventory count correction',
    ]);
    $request->headers->set('referer', route('admin.productos.show', $producto));
    $request->setLaravelSession($this->app['session.store']);
    $request->setUserResolver(fn (): User => $admin);

    $response = app(ProductoController::class)->actualizarStock($request, $staleProducto);

    expect($response->getSession()->get('error'))->toBe('❌ Stock insuficiente para realizar esta operación.');

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'stock' => 3,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);
});
