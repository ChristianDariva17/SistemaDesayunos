<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\User;
use App\Support\MoneyDecimal;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;

it('passes canonical decimal sales totals and groups to the report view', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $cliente = Cliente::create(['nombre' => 'Decimal', 'estado' => 'activo']);
    $empleado = Empleado::create(['user_id' => $admin->id, 'nombre' => 'Admin', 'rol_operativo' => 'admin', 'estado' => 'activo']);

    foreach ([['2026-07-01', 'completado', '0.10'], ['2026-07-01', 'pendiente', '0.20'], ['2026-07-02', 'completado', '0.30']] as [$fecha, $estado, $total]) {
        Pedido::create(['cliente_id' => $cliente->id, 'empleado_id' => $empleado->id, 'fecha' => $fecha, 'hora' => '10:00:00', 'total' => $total, 'estado' => $estado]);
    }

    $document = Mockery::mock(DomPdfDocument::class);
    $document->shouldReceive('setPaper', 'A4', 'portrait')->andReturnSelf();
    $document->shouldReceive('setOption')->times(3)->andReturnSelf();
    $document->shouldReceive('stream')->andReturn(response('', 200));
    Pdf::shouldReceive('loadView')->once()->with('admin.reportes.ventas', Mockery::on(function (array $data): bool {
        expect($data['totalVentas'])->toBe('0.60')
            ->and($data['totalCompletados'])->toBe('0.40')
            ->and($data['totalPendientes'])->toBe('0.20')
            ->and($data['ticketPromedio'])->toBe('0.20')
            ->and($data['topClientes']->first()['total'])->toBe('0.60')
            ->and($data['ventasPorDia']->pluck('total')->all())->toBe(['0.30', '0.30'])
            ->and(MoneyDecimal::sum([$data['subtotalGeneral'], $data['igvGeneral']]))->toBe($data['totalVentas']);

        return true;
    }))->andReturn($document);

    $this->actingAs($admin)->get(route('admin.reportes.ventas', [
        'accion' => 'ver', 'fecha_inicio' => '2026-07-01', 'fecha_fin' => '2026-07-03',
    ]))->assertOk();
});

it('passes zero-valued canonical sales totals for an empty report', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $document = Mockery::mock(DomPdfDocument::class);
    $document->shouldIgnoreMissing()->andReturnSelf();
    $document->shouldReceive('stream')->andReturn(response('', 200));
    Pdf::shouldReceive('loadView')->with('admin.reportes.ventas', Mockery::on(function (array $data): bool {
        expect($data['totalVentas'])->toBe('0.00')->and($data['ticketPromedio'])->toBe('0.00');

        return true;
    }))->andReturn($document);

    $this->actingAs($admin)->get(route('admin.reportes.ventas', [
        'accion' => 'ver', 'fecha_inicio' => '2026-07-01', 'fecha_fin' => '2026-07-01',
    ]))->assertOk();
});
