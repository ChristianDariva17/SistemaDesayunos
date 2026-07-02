<?php

declare(strict_types=1);

use App\Actions\Stock\RegisterStockMovementAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function stockMovimientoTestProducto(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Ledger Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ], $attributes));
}

function stockMovimientoTestPedido(array $attributes = []): Pedido
{
    $cliente = Cliente::create([
        'nombre' => 'Ledger',
        'apellido' => 'Cliente',
        'email' => 'ledger.cliente.' . uniqid() . '@example.com',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Ledger Worker',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    return Pedido::create(array_merge([
        'numero_pedido' => 'PED-' . uniqid(),
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-01',
        'hora' => '08:30:00',
        'total' => 12.50,
        'estado' => 'pendiente',
        'observaciones' => null,
    ], $attributes));
}

it('persists a stock movement with product pedido and user relations', function (): void {
    $producto = stockMovimientoTestProducto();
    $pedido = stockMovimientoTestPedido();
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $movimiento = StockMovimiento::create([
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'pedido_numero' => $pedido->numero_pedido,
        'user_id' => $user->id,
        'tipo' => 'salida',
        'cantidad' => 3,
        'stock_anterior' => 10,
        'stock_nuevo' => 7,
        'motivo' => 'Pedido registrado',
    ]);

    $movimiento->load(['producto', 'pedido', 'user']);

    expect($movimiento->producto->is($producto))->toBeTrue()
        ->and($movimiento->pedido->is($pedido))->toBeTrue()
        ->and($movimiento->user->is($user))->toBeTrue()
        ->and($producto->stockMovimientos()->whereKey($movimiento->id)->exists())->toBeTrue()
        ->and($pedido->stockMovimientos()->whereKey($movimiento->id)->exists())->toBeTrue()
        ->and($user->stockMovimientos()->whereKey($movimiento->id)->exists())->toBeTrue();

    $this->assertDatabaseHas('stock_movimientos', [
        'id' => $movimiento->id,
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'pedido_numero' => $pedido->numero_pedido,
        'user_id' => $user->id,
        'tipo' => 'salida',
        'cantidad' => 3,
        'stock_anterior' => 10,
        'stock_nuevo' => 7,
        'motivo' => 'Pedido registrado',
    ]);
});

it('allows nullable pedido and user for manual or system adjustments', function (): void {
    $producto = stockMovimientoTestProducto([
        'stock' => 5,
    ]);

    $movimiento = StockMovimiento::create([
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'user_id' => null,
        'tipo' => 'ajuste',
        'cantidad' => 2,
        'stock_anterior' => 5,
        'stock_nuevo' => 7,
        'motivo' => 'System reconciliation',
    ]);

    expect($movimiento->pedido)->toBeNull()
        ->and($movimiento->user)->toBeNull()
        ->and($movimiento->tipo)->toBe('ajuste')
        ->and($movimiento->cantidad)->toBe(2)
        ->and($movimiento->stock_anterior)->toBe(5)
        ->and($movimiento->stock_nuevo)->toBe(7)
        ->and($movimiento->motivo)->toBe('System reconciliation');

    $this->assertDatabaseHas('stock_movimientos', [
        'id' => $movimiento->id,
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'user_id' => null,
        'tipo' => 'ajuste',
        'cantidad' => 2,
        'stock_anterior' => 5,
        'stock_nuevo' => 7,
        'motivo' => 'System reconciliation',
    ]);
});

it('registers a valid stock movement through the stock action', function (): void {
    $producto = stockMovimientoTestProducto();
    $pedido = stockMovimientoTestPedido();
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $movimiento = app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_SALIDA,
        cantidad: 3,
        stockAnterior: 10,
        stockNuevo: 7,
        pedido: $pedido,
        user: $user,
        motivo: 'Pedido registrado',
    );

    expect($movimiento)->toBeInstanceOf(StockMovimiento::class)
        ->and($movimiento->producto_id)->toBe($producto->id)
        ->and($movimiento->pedido_id)->toBe($pedido->id)
        ->and($movimiento->pedido_numero)->toBe($pedido->numero_pedido)
        ->and($movimiento->user_id)->toBe($user->id)
        ->and($movimiento->tipo)->toBe(StockMovimiento::TIPO_SALIDA)
        ->and($movimiento->cantidad)->toBe(3)
        ->and($movimiento->stock_anterior)->toBe(10)
        ->and($movimiento->stock_nuevo)->toBe(7)
        ->and($movimiento->motivo)->toBe('Pedido registrado');

    $this->assertDatabaseHas('stock_movimientos', [
        'id' => $movimiento->id,
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'pedido_numero' => $pedido->numero_pedido,
        'user_id' => $user->id,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 3,
        'stock_anterior' => 10,
        'stock_nuevo' => 7,
        'motivo' => 'Pedido registrado',
    ]);
});

it('keeps the historical pedido number when a related pedido is deleted', function (): void {
    $producto = stockMovimientoTestProducto();
    $pedido = stockMovimientoTestPedido([
        'numero_pedido' => 'PED-202607-HISTORY',
    ]);

    $movimiento = app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_SALIDA,
        cantidad: 1,
        stockAnterior: 10,
        stockNuevo: 9,
        pedido: $pedido,
        motivo: 'Pedido registered',
    );

    $pedido->delete();

    $this->assertDatabaseHas('stock_movimientos', [
        'id' => $movimiento->id,
        'pedido_id' => null,
        'pedido_numero' => 'PED-202607-HISTORY',
    ]);
});

it('backfills historical pedido numbers for existing stock movements during migration', function (): void {
    $migration = include database_path('migrations/2026_07_01_000001_add_pedido_numero_to_stock_movimientos_table.php');
    $migration->down();

    $producto = stockMovimientoTestProducto();
    $pedido = stockMovimientoTestPedido([
        'numero_pedido' => 'PED-202607-BACKFILL',
    ]);

    $movimientoId = DB::table('stock_movimientos')->insertGetId([
        'producto_id' => $producto->id,
        'pedido_id' => $pedido->id,
        'user_id' => null,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 1,
        'stock_anterior' => 10,
        'stock_nuevo' => 9,
        'motivo' => 'Existing movement before pedido number snapshot',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration->up();

    expect(Schema::hasColumn('stock_movimientos', 'pedido_numero'))->toBeTrue();

    $this->assertDatabaseHas('stock_movimientos', [
        'id' => $movimientoId,
        'pedido_id' => $pedido->id,
        'pedido_numero' => 'PED-202607-BACKFILL',
    ]);

    $pedido->delete();

    $this->assertDatabaseHas('stock_movimientos', [
        'id' => $movimientoId,
        'pedido_id' => null,
        'pedido_numero' => 'PED-202607-BACKFILL',
    ]);
});

it('rejects invalid stock movement tipo through the stock action', function (): void {
    $producto = stockMovimientoTestProducto();

    expect(fn () => app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: 'invalid',
        cantidad: 1,
        stockAnterior: 10,
        stockNuevo: 9,
    ))->toThrow(InvalidArgumentException::class, 'Invalid stock movement tipo.');

    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects zero stock movement quantity through the stock action', function (): void {
    $producto = stockMovimientoTestProducto();

    expect(fn () => app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_AJUSTE,
        cantidad: 0,
        stockAnterior: 10,
        stockNuevo: 10,
    ))->toThrow(InvalidArgumentException::class, 'Stock movement cantidad must be greater than 0.');

    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects negative stock movement quantity through the stock action', function (): void {
    $producto = stockMovimientoTestProducto();

    expect(fn () => app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_AJUSTE,
        cantidad: -1,
        stockAnterior: 10,
        stockNuevo: 9,
    ))->toThrow(InvalidArgumentException::class, 'Stock movement cantidad must be greater than 0.');

    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects unsaved producto through the stock action', function (): void {
    $producto = new Producto([
        'nombre' => 'Unsaved Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    expect(fn () => app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_AJUSTE,
        cantidad: 1,
        stockAnterior: 10,
        stockNuevo: 9,
    ))->toThrow(InvalidArgumentException::class, 'A persisted producto is required to register a stock movement.');

    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects producto with assigned id but not persisted through the stock action', function (): void {
    $producto = new Producto([
        'nombre' => 'Assigned Id Sandwich',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);
    $producto->id = 12345;

    expect(fn () => app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_AJUSTE,
        cantidad: 1,
        stockAnterior: 10,
        stockNuevo: 9,
    ))->toThrow(InvalidArgumentException::class, 'A persisted producto is required to register a stock movement.');

    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('rejects negative stock values through the stock action', function (int $stockAnterior, int $stockNuevo): void {
    $producto = stockMovimientoTestProducto();

    expect(fn () => app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_AJUSTE,
        cantidad: 1,
        stockAnterior: $stockAnterior,
        stockNuevo: $stockNuevo,
    ))->toThrow(InvalidArgumentException::class, 'Stock movement stock values must be non-negative.');

    $this->assertDatabaseCount('stock_movimientos', 0);
})->with([
    'negative previous stock' => [-1, 0],
    'negative new stock' => [0, -1],
]);

it('registers a stock movement without pedido or user through the stock action', function (): void {
    $producto = stockMovimientoTestProducto([
        'stock' => 5,
    ]);

    $movimiento = app(RegisterStockMovementAction::class)->handle(
        producto: $producto,
        tipo: StockMovimiento::TIPO_AJUSTE,
        cantidad: 2,
        stockAnterior: 5,
        stockNuevo: 7,
        motivo: 'System reconciliation',
    );

    expect($movimiento->pedido_id)->toBeNull()
        ->and($movimiento->pedido_numero)->toBeNull()
        ->and($movimiento->user_id)->toBeNull()
        ->and($movimiento->tipo)->toBe(StockMovimiento::TIPO_AJUSTE)
        ->and($movimiento->cantidad)->toBe(2)
        ->and($movimiento->stock_anterior)->toBe(5)
        ->and($movimiento->stock_nuevo)->toBe(7)
        ->and($movimiento->motivo)->toBe('System reconciliation');

    $this->assertDatabaseHas('stock_movimientos', [
        'id' => $movimiento->id,
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'pedido_numero' => null,
        'user_id' => null,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 2,
        'stock_anterior' => 5,
        'stock_nuevo' => 7,
        'motivo' => 'System reconciliation',
    ]);
});
