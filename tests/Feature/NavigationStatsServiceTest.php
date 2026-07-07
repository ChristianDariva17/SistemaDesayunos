<?php

declare(strict_types=1);

use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\NavigationStatsService;
use App\View\Composers\AppLayoutComposer;

it('counts low-stock products and pending orders for the app layout', function (): void {
    Producto::create([
        'nombre' => 'Low stock item',
        'categoria' => 'bebida',
        'precio' => 5.00,
        'stock' => 9,
        'estado' => 'activo',
    ]);

    Producto::create([
        'nombre' => 'Threshold item',
        'categoria' => 'bebida',
        'precio' => 5.00,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Layout Tester',
        'rol_operativo' => 'otros',
        'estado' => 'activo',
    ]);

    Pedido::create([
        'empleado_id' => $empleado->id,
        'estado' => 'pendiente',
        'fecha' => now()->toDateString(),
        'hora' => now()->format('H:i:s'),
        'total' => 10.00,
    ]);

    Pedido::create([
        'empleado_id' => $empleado->id,
        'estado' => 'completado',
        'fecha' => now()->toDateString(),
        'hora' => now()->format('H:i:s'),
        'total' => 20.00,
    ]);

    $stats = app(NavigationStatsService::class)->getLayoutStats();

    expect($stats)->toBe([
        'stockBajo' => 1,
        'pedidosPendientes' => 1,
    ]);
});

it('shares navigation stats with the app layout view', function (): void {
    Producto::create([
        'nombre' => 'Low stock item',
        'categoria' => 'bebida',
        'precio' => 5.00,
        'stock' => 3,
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Layout Tester',
        'rol_operativo' => 'otros',
        'estado' => 'activo',
    ]);

    Pedido::create([
        'empleado_id' => $empleado->id,
        'estado' => 'pendiente',
        'fecha' => now()->toDateString(),
        'hora' => now()->format('H:i:s'),
        'total' => 10.00,
    ]);

    $view = view('layouts.app');

    app(AppLayoutComposer::class)->compose($view);

    expect($view->getData())
        ->toHaveKey('navigationStats', [
            'stockBajo' => 1,
            'pedidosPendientes' => 1,
        ])
        ->toHaveKey('stockBajo', 1)
        ->toHaveKey('pedidosPendientes', 1);
});

it('renders the app layout with composer-provided counters', function (): void {
    Producto::create([
        'nombre' => 'Low stock item',
        'categoria' => 'bebida',
        'precio' => 5.00,
        'stock' => 2,
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Layout Tester',
        'rol_operativo' => 'otros',
        'estado' => 'activo',
    ]);

    Pedido::create([
        'empleado_id' => $empleado->id,
        'estado' => 'pendiente',
        'fecha' => now()->toDateString(),
        'hora' => now()->format('H:i:s'),
        'total' => 10.00,
    ]);

    $rendered = view('layouts.app')->render();

    expect($rendered)
        ->toContain('1 pedidos pendientes')
        ->toContain('1 productos con stock bajo');
});
