<?php

declare(strict_types=1);

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;

function stockMovimientosReportProducto(string $nombre): Producto
{
    return Producto::create([
        'nombre' => $nombre,
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);
}

function stockMovimientosReportMovement(array $attributes = []): StockMovimiento
{
    $movimiento = StockMovimiento::create(array_merge([
        'producto_id' => stockMovimientosReportProducto('Report Sandwich')->id,
        'pedido_id' => null,
        'pedido_numero' => null,
        'user_id' => null,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 1,
        'stock_anterior' => 10,
        'stock_nuevo' => 11,
        'motivo' => 'Report test movement',
    ], $attributes));

    if (array_key_exists('created_at', $attributes)) {
        $movimiento->forceFill([
            'created_at' => $attributes['created_at'],
            'updated_at' => $attributes['created_at'],
        ])->save();
    }

    return $movimiento;
}

it('requires an administrator to access the stock movements report', function (): void {
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $this->get(route('admin.reportes.stock-movimientos'))
        ->assertRedirect(route('login', absolute: false));

    $this->actingAs($worker)
        ->get(route('admin.reportes.stock-movimientos'))
        ->assertForbidden();
});

it('renders stock movement rows with pedido snapshot and actor details', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
        'name' => 'Admin Reporter',
    ]);
    $actor = User::factory()->create([
        'rol' => 'trabajador',
        'name' => 'Warehouse Actor',
        'email' => 'warehouse.actor@example.com',
    ]);
    $producto = stockMovimientosReportProducto('Audit Coffee');

    stockMovimientosReportMovement([
        'producto_id' => $producto->id,
        'pedido_numero' => 'PED-SNAPSHOT-001',
        'user_id' => $actor->id,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 3,
        'stock_anterior' => 10,
        'stock_nuevo' => 7,
        'motivo' => 'Pedido stock reservation',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reportes.stock-movimientos'))
        ->assertOk()
        ->assertSee('Audit Coffee')
        ->assertSee('PED-SNAPSHOT-001')
        ->assertSee('Warehouse Actor')
        ->assertSee('warehouse.actor@example.com')
        ->assertSee('Salida')
        ->assertSee('Pedido stock reservation');
});

it('filters stock movements by product type and actor', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $matchingActor = User::factory()->create(['rol' => 'trabajador', 'name' => 'Matching Actor']);
    $otherActor = User::factory()->create(['rol' => 'trabajador', 'name' => 'Other Actor']);
    $matchingProduct = stockMovimientosReportProducto('Filtered Toast');
    $otherProduct = stockMovimientosReportProducto('Hidden Juice');

    stockMovimientosReportMovement([
        'producto_id' => $matchingProduct->id,
        'user_id' => $matchingActor->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'motivo' => 'Visible movement',
    ]);
    stockMovimientosReportMovement([
        'producto_id' => $otherProduct->id,
        'user_id' => $otherActor->id,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'motivo' => 'Hidden movement',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reportes.stock-movimientos', [
            'producto_id' => $matchingProduct->id,
            'tipo' => StockMovimiento::TIPO_ENTRADA,
            'user_id' => $matchingActor->id,
        ]))
        ->assertOk()
        ->assertSee('Filtered Toast')
        ->assertSee('Visible movement')
        ->assertDontSee('Hidden movement');
});

it('filters stock movements by date range', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    stockMovimientosReportMovement([
        'motivo' => 'Movement inside range',
        'created_at' => '2026-07-02 09:00:00',
    ]);
    stockMovimientosReportMovement([
        'motivo' => 'Movement outside range',
        'created_at' => '2026-06-25 09:00:00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reportes.stock-movimientos', [
            'fecha_inicio' => '2026-07-01',
            'fecha_fin' => '2026-07-03',
        ]))
        ->assertOk()
        ->assertSee('Movement inside range')
        ->assertDontSee('Movement outside range');
});

it('filters stock movements by end date without requiring a start date', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    stockMovimientosReportMovement([
        'motivo' => 'Movement before end date',
        'created_at' => '2026-07-02 09:00:00',
    ]);
    stockMovimientosReportMovement([
        'motivo' => 'Movement after end date',
        'created_at' => '2026-07-04 09:00:00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reportes.stock-movimientos', [
            'fecha_fin' => '2026-07-03',
        ]))
        ->assertOk()
        ->assertSee('Movement before end date')
        ->assertDontSee('Movement after end date');
});

it('validates end date is not before start date when both filters are present', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->from(route('admin.reportes.stock-movimientos'))
        ->get(route('admin.reportes.stock-movimientos', [
            'fecha_inicio' => '2026-07-03',
            'fecha_fin' => '2026-07-02',
        ]))
        ->assertRedirect(route('admin.reportes.stock-movimientos'))
        ->assertSessionHasErrors(['fecha_fin']);
});

it('validates malformed stock movement report date filters', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->from(route('admin.reportes.stock-movimientos'))
        ->get(route('admin.reportes.stock-movimientos', [
            'fecha_inicio' => 'not-a-date',
            'fecha_fin' => 'also-not-a-date',
        ]))
        ->assertRedirect(route('admin.reportes.stock-movimientos'))
        ->assertSessionHasErrors(['fecha_inicio', 'fecha_fin']);
});

it('rejects an invalid stock movement type filter', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->from(route('admin.reportes.stock-movimientos'))
        ->get(route('admin.reportes.stock-movimientos', [
            'tipo' => 'invalid',
        ]))
        ->assertRedirect(route('admin.reportes.stock-movimientos'))
        ->assertSessionHasErrors(['tipo']);
});
