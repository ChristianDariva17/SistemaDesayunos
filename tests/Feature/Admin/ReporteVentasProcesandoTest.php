<?php

use App\Models\Pedido;
use App\Models\Producto;
use Carbon\Carbon;

it('renders procesando totals in the ventas report view', function (): void {
    $pedido = new Pedido([
        'id' => 1,
        'cliente_id' => 10,
        'fecha' => '2026-06-01',
        'hora' => '10:15:00',
        'total' => 45.50,
        'estado' => 'procesando',
    ]);

    $pedido->created_at = Carbon::parse('2026-07-03 18:45:00');
    $pedido->setRelation('cliente', (object) [
        'nombre' => 'Ana Pérez',
        'telefono' => '999999999',
    ]);

    $html = view('admin.reportes.ventas', [
        'pedidos' => collect([$pedido]),
        'totalVentas' => 45.50,
        'cantidadPedidos' => 1,
        'fechaInicio' => '2026-06-01',
        'fechaFin' => '2026-06-01',
    ])->render();

    $this->assertMatchesRegularExpression(
        '/<td class="status-box status-procesando"[^>]*>.*?<div class="status-number">1<\/div>.*?<div class="status-label">Procesando<\/div>.*?<div class="status-amount">S\/ 45\.50<\/div>/s',
        $html
    );

    expect($html)->toContain('01/06/2026')
        ->toContain('10:15 AM');
});

it('counts ventas items from the canonical productos relation', function (): void {
    $pedido = new Pedido([
        'id' => 7,
        'cliente_id' => 10,
        'fecha' => '2026-06-01',
        'hora' => '10:15:00',
        'total' => 32.00,
        'estado' => 'completado',
    ]);

    $pedido->created_at = Carbon::parse('2026-07-03 18:45:00');
    $pedido->setRelation('cliente', (object) [
        'nombre' => 'Ana Pérez',
        'telefono' => '999999999',
    ]);
    $pedido->setRelation('productos', collect([
        (new Producto)->forceFill(['id' => 1, 'nombre' => 'Cafe']),
        (new Producto)->forceFill(['id' => 2, 'nombre' => 'Sandwich']),
    ]));

    $html = view('admin.reportes.ventas', [
        'pedidos' => collect([$pedido]),
        'totalVentas' => 32.00,
        'cantidadPedidos' => 1,
        'fechaInicio' => '2026-06-01',
        'fechaFin' => '2026-06-01',
    ])->render();

    $this->assertMatchesRegularExpression('/<td class="text-center">\s*<strong>2<\/strong>\s*<\/td>/s', $html);
});
