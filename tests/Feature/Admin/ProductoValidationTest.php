<?php

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Requests\Admin\UpdateProductoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    $request->setRouteResolver(fn (): object => new class ($staleProducto) {
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
