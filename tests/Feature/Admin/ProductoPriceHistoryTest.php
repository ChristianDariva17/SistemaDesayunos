<?php

declare(strict_types=1);

use App\Actions\Pedido\CreatePedidoAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoPriceHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('backfills existing product prices when the price history migration runs', function (): void {
    Schema::dropIfExists('producto_price_histories');

    $createdAt = now()->subDay()->startOfSecond();

    $productoId = DB::table('productos')->insertGetId([
        'nombre' => 'Backfilled coffee',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 5,
        'estado' => 'activo',
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);

    $migration = require database_path('migrations/2026_07_04_124500_create_producto_price_histories_table.php');
    $migration->up();

    $this->assertDatabaseHas('producto_price_histories', [
        'producto_id' => $productoId,
        'precio' => 12.50,
        'effective_from' => $createdAt->toDateTimeString(),
        'effective_to' => null,
    ]);
});

it('creates an initial price history entry for a new product', function (): void {
    $producto = Producto::create([
        'nombre' => 'New coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $this->assertDatabaseHas('producto_price_histories', [
        'producto_id' => $producto->id,
        'precio' => 10.00,
        'effective_to' => null,
    ]);
});

it('creates a new price history entry only when the product price changes', function (): void {
    $producto = Producto::create([
        'nombre' => 'Editable coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $producto->update([
        'nombre' => 'Editable premium coffee',
        'precio' => 12.50,
    ]);

    expect($producto->priceHistories()->count())->toBe(2);

    $this->assertDatabaseHas('producto_price_histories', [
        'producto_id' => $producto->id,
        'precio' => 10.00,
    ]);
    $this->assertDatabaseHas('producto_price_histories', [
        'producto_id' => $producto->id,
        'precio' => 12.50,
        'effective_to' => null,
    ]);
});

it('does not create a duplicate price history entry for same-price product updates', function (): void {
    $producto = Producto::create([
        'nombre' => 'Stable coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $producto->update([
        'nombre' => 'Stable renamed coffee',
        'stock' => 8,
        'precio' => 10.00,
    ]);

    expect($producto->priceHistories()->count())->toBe(1);
});

it('allows deleting a product with price history when it has no pedidos', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = Producto::create([
        'nombre' => 'Deletable coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $producto->update([
        'precio' => 12.50,
    ]);

    expect($producto->pedidos()->exists())->toBeFalse()
        ->and($producto->priceHistories()->count())->toBe(2);

    $response = $this->actingAs($admin)
        ->from(route('admin.productos.show', $producto))
        ->delete(route('admin.productos.destroy', $producto));

    $response->assertRedirect(route('admin.productos.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('productos', [
        'id' => $producto->id,
    ]);
    $this->assertDatabaseMissing('producto_price_histories', [
        'producto_id' => $producto->id,
    ]);
});

it('rolls back the product price when price history persistence fails', function (): void {
    $producto = Producto::create([
        'nombre' => 'Rollback coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    ProductoPriceHistory::creating(function (ProductoPriceHistory $history): void {
        if ((float) $history->precio === 12.50) {
            throw new RuntimeException('Simulated price history persistence failure.');
        }
    });

    try {
        expect(fn () => $producto->update(['precio' => 12.50]))
            ->toThrow(RuntimeException::class, 'Simulated price history persistence failure.');
    } finally {
        ProductoPriceHistory::flushEventListeners();
    }

    $producto->refresh();

    expect((float) $producto->precio)->toBe(10.00)
        ->and($producto->priceHistories()->count())->toBe(1)
        ->and((float) $producto->priceHistories()->firstOrFail()->precio)->toBe(10.00)
        ->and($producto->priceHistories()->whereNull('effective_to')->count())->toBe(1);
});

it('keeps pedido detail historical price after the product price later changes', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Test client',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Test employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
    $producto = Producto::create([
        'nombre' => 'Pedido coffee',
        'categoria' => 'bebida',
        'precio' => 10.00,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $pedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ]);

    $producto->update([
        'precio' => 15.00,
    ]);

    $storedPedido = Pedido::query()->with('productos')->findOrFail($pedido->id);

    expect((float) $storedPedido->productos->first()->pivot->precio_unitario)->toBe(10.00)
        ->and((float) $storedPedido->productos->first()->pivot->subtotal)->toBe(20.00)
        ->and(ProductoPriceHistory::query()->where('producto_id', $producto->id)->count())->toBe(2);
});
