<?php

declare(strict_types=1);

use App\Http\Requests\Admin\StoreStockEntryRequest;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Support\InventoryLimits;

function stockEntryProducto(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Receiving Coffee',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ], $attributes));
}

it('requires an administrator to access stock entry routes', function (): void {
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);
    $producto = stockEntryProducto(['stock' => 6]);

    $this->get(route('admin.stock-entries.create'))
        ->assertRedirect(route('login', absolute: false));

    $this->post(route('admin.stock-entries.store'), [])
        ->assertRedirect(route('login', absolute: false));

    $this->actingAs($worker)
        ->get(route('admin.stock-entries.create'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => 3,
            'motivo' => 'Unauthorized receiving',
        ])
        ->assertForbidden();

    expect($producto->refresh()->stock)->toBe(6);
    $this->assertDatabaseCount('stock_movimientos', 0);
});

it('authorizes stock entries through the updateStock policy', function (string $role, bool $authorized): void {
    $user = User::factory()->make(['rol' => $role]);
    $producto = stockEntryProducto();
    $request = StoreStockEntryRequest::create('/stock-entries', 'POST');
    $request->setUserResolver(static fn (): User => $user);

    expect($user->can('updateStock', Producto::class))->toBe($authorized)
        ->and($user->can('updateStock', $producto))->toBe($authorized)
        ->and($request->authorize())->toBe($authorized);
})->with([
    'administrator' => ['administrador', true],
    'legacy administrator' => ['admin', true],
    'worker' => ['trabajador', false],
    'legacy worker' => ['empleado', false],
]);

it('denies stock entry authorization to guests', function (): void {
    $request = StoreStockEntryRequest::create('/stock-entries', 'POST');
    $request->setUserResolver(static fn (): null => null);

    expect($request->authorize())->toBeFalse();
});

it('renders the stock entry form for administrators', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    stockEntryProducto(['nombre' => 'Entry Toast', 'stock' => 7]);

    $this->actingAs($admin)
        ->get(route('admin.stock-entries.create'))
        ->assertOk()
        ->assertSee('Registrar Entrada de Stock')
        ->assertSee('Entry Toast')
        ->assertSee('stock actual: 7');
});

it('increments product stock and records an entrada ledger movement', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => 4,
            'motivo' => 'Weekly receiving',
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    expect($producto->refresh()->stock)->toBe(9);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'pedido_numero' => null,
        'user_id' => $admin->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 4,
        'stock_anterior' => 5,
        'stock_nuevo' => 9,
        'motivo' => 'Weekly receiving',
    ]);
});

it('stores blank stock entry motivo as null', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => 4,
            'motivo' => "\t  \n",
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 4,
        'motivo' => null,
    ]);
});

it('stores unicode blank stock entry motivo as null', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => 4,
            'motivo' => "\u{00A0}\u{2003}",
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 4,
        'motivo' => null,
    ]);
});

it('stores trimmed stock entry motivo', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => 4,
            'motivo' => '  Weekly receiving  ',
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 4,
        'motivo' => 'Weekly receiving',
    ]);
});

it('trims unicode whitespace around stock entry motivo', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => 4,
            'motivo' => "\u{00A0}Weekly receiving\u{2003}",
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 4,
        'motivo' => 'Weekly receiving',
    ]);
});

it('validates product and quantity before registering a stock entry', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-entries.create'))
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => 999999,
            'cantidad' => 0,
            'motivo' => str_repeat('x', 256),
        ])
        ->assertRedirect(route('admin.stock-entries.create'))
        ->assertSessionHasErrors(['producto_id', 'cantidad', 'motivo']);
});

it('accepts the maximum safe stock entry quantity', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 0,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => InventoryLimits::MAX_STOCK_LEVEL,
            'motivo' => 'Maximum receiving',
        ])
        ->assertRedirect(route('admin.reportes.stock-movimientos', [
            'producto_id' => $producto->id,
        ]));

    expect($producto->refresh()->stock)->toBe(InventoryLimits::MAX_STOCK_LEVEL);

    $this->assertDatabaseHas('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => InventoryLimits::MAX_STOCK_LEVEL,
        'stock_anterior' => 0,
        'stock_nuevo' => InventoryLimits::MAX_STOCK_LEVEL,
    ]);
});

it('rejects stock entry quantities above the safe integer bound', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 0,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-entries.create'))
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => InventoryLimits::MAX_STOCK_LEVEL + 1,
        ])
        ->assertRedirect(route('admin.stock-entries.create'))
        ->assertSessionHasErrors(['cantidad']);

    expect($producto->refresh()->stock)->toBe(0);
    $this->assertDatabaseMissing('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
    ]);
});

it('rejects stock entries that would overflow the resulting product stock', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
    $producto = stockEntryProducto([
        'stock' => 1,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.stock-entries.create'))
        ->post(route('admin.stock-entries.store'), [
            'producto_id' => $producto->id,
            'cantidad' => InventoryLimits::MAX_STOCK_LEVEL,
        ])
        ->assertRedirect(route('admin.stock-entries.create'))
        ->assertSessionHasErrors(['cantidad']);

    expect($producto->refresh()->stock)->toBe(1);
    $this->assertDatabaseMissing('stock_movimientos', [
        'producto_id' => $producto->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
    ]);
});
