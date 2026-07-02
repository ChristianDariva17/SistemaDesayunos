<?php

declare(strict_types=1);

use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;

function inventorySummaryReportProduct(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre' => 'Summary Coffee',
        'categoria' => 'bebidas',
        'stock' => 10,
        'stock_minimo' => 5,
        'estado' => 'activo',
        'precio' => 12.50,
    ], $attributes));
}

function inventorySummaryReportMovement(array $attributes = []): StockMovimiento
{
    $movement = StockMovimiento::create(array_merge([
        'producto_id' => inventorySummaryReportProduct()->id,
        'pedido_id' => null,
        'pedido_numero' => null,
        'user_id' => null,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 1,
        'stock_anterior' => 9,
        'stock_nuevo' => 10,
        'motivo' => 'Inventory summary test movement',
    ], $attributes));

    if (array_key_exists('created_at', $attributes)) {
        $movement->forceFill([
            'created_at' => $attributes['created_at'],
            'updated_at' => $attributes['created_at'],
        ])->save();
    }

    return $movement;
}

it('requires an administrator to access the inventory summary report', function (): void {
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $this->get(route('admin.reportes.resumen-inventario'))
        ->assertRedirect(route('login', absolute: false));

    $this->actingAs($worker)
        ->get(route('admin.reportes.resumen-inventario'))
        ->assertForbidden();
});

it('shows inventory summary aggregate numbers and latest movement by product', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $product = inventorySummaryReportProduct([
        'nombre' => 'Aggregate Granola',
        'categoria' => 'desayuno',
        'stock' => 8,
        'stock_minimo' => 10,
    ]);

    inventorySummaryReportMovement([
        'producto_id' => $product->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 20,
        'created_at' => '2026-07-01 09:00:00',
    ]);
    inventorySummaryReportMovement([
        'producto_id' => $product->id,
        'tipo' => StockMovimiento::TIPO_SALIDA,
        'cantidad' => 7,
        'created_at' => '2026-07-02 09:00:00',
    ]);
    inventorySummaryReportMovement([
        'producto_id' => $product->id,
        'tipo' => StockMovimiento::TIPO_AJUSTE,
        'cantidad' => 3,
        'created_at' => '2026-07-03 09:00:00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reportes.resumen-inventario'))
        ->assertOk()
        ->assertSee('Aggregate Granola')
        ->assertSee('Desayuno')
        ->assertSee('8')
        ->assertSee('10')
        ->assertSee('20')
        ->assertSee('7')
        ->assertSee('3')
        ->assertSee('03/07/2026')
        ->assertSee('Ajuste')
        ->assertSee('Stock bajo');
});

it('shows zero aggregates and no latest movement for products without stock movements', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    inventorySummaryReportProduct([
        'nombre' => 'No Movement Tea',
        'stock' => 12,
        'stock_minimo' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reportes.resumen-inventario'))
        ->assertOk()
        ->assertSee('No Movement Tea')
        ->assertSee('Sin movimientos')
        ->assertSee('Sin mínimo')
        ->assertSeeInOrder(['Entradas', 'Salidas', 'Ajustes', '0', '0', '0']);
});

it('filters the inventory summary by product name category and status', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    inventorySummaryReportProduct([
        'nombre' => 'Visible Apple Juice',
        'categoria' => 'bebidas',
        'estado' => 'activo',
    ]);
    inventorySummaryReportProduct([
        'nombre' => 'Hidden Apple Bread',
        'categoria' => 'panaderia',
        'estado' => 'activo',
    ]);
    inventorySummaryReportProduct([
        'nombre' => 'Inactive Apple Soda',
        'categoria' => 'bebidas',
        'estado' => 'inactivo',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reportes.resumen-inventario', [
            'buscar' => 'Apple',
            'categoria' => 'bebidas',
            'estado' => 'activo',
        ]))
        ->assertOk()
        ->assertSee('Visible Apple Juice')
        ->assertDontSee('Hidden Apple Bread')
        ->assertDontSee('Inactive Apple Soda');
});
