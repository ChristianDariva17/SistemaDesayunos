<?php

declare(strict_types=1);

use App\Actions\Cash\CloseDailyCashRegisterAction;
use App\Models\Cliente;
use App\Models\DailyCashClosure;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('stores a daily cash closure snapshot from completed pedidos for the business date', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    createClosurePedido('PED-CLOSE-001', '2026-07-04', 'completado', 100.25, 'efectivo');
    createClosurePedido('PED-CLOSE-002', '2026-07-04', 'completado', 50.75, 'tarjeta');
    createClosurePedido('PED-CLOSE-003', '2026-07-04', 'completado', 20.00, 'efectivo');
    createClosurePedido('PED-CLOSE-004', '2026-07-04', 'pendiente', 99.99, 'efectivo');
    createClosurePedido('PED-CLOSE-005', '2026-07-04', 'procesando', 42.00, 'transferencia');
    createClosurePedido('PED-CLOSE-006', '2026-07-04', 'cancelado', 15.00, 'otro');
    createClosurePedido('PED-CLOSE-007', '2026-07-05', 'completado', 999.00, 'efectivo');

    $closure = $this->actingAs($user)
        ->app->make(CloseDailyCashRegisterAction::class)
        ->handle('2026-07-04');

    expect($closure)->toBeInstanceOf(DailyCashClosure::class)
        ->and($closure->business_date->toDateString())->toBe('2026-07-04')
        ->and($closure->total_orders)->toBe(6)
        ->and((float) $closure->total_revenue)->toBe(171.0)
        ->and($closure->settled_order_count)->toBe(3)
        ->and($closure->pending_order_count)->toBe(2)
        ->and($closure->cancelled_order_count)->toBe(1)
        ->and($closure->closed_by_user_id)->toBe($user->id)
        ->and($closure->payment_method_totals)->toBe([
            'efectivo' => [
                'count' => 2,
                'total' => '120.25',
            ],
            'tarjeta' => [
                'count' => 1,
                'total' => '50.75',
            ],
        ]);

    $this->assertDatabaseHas('daily_cash_closures', [
        'business_date' => '2026-07-04 00:00:00',
        'total_orders' => 6,
        'total_revenue' => 171.00,
        'settled_order_count' => 3,
        'pending_order_count' => 2,
        'cancelled_order_count' => 1,
        'closed_by_user_id' => $user->id,
    ]);
});

it('prevents duplicate daily cash closures for the same business date', function (): void {
    createClosurePedido('PED-CLOSE-DUP1', '2026-07-04', 'completado', 25.00, 'efectivo');

    app(CloseDailyCashRegisterAction::class)->handle('2026-07-04', null);

    expect(fn (): DailyCashClosure => app(CloseDailyCashRegisterAction::class)->handle('2026-07-04', null))
        ->toThrow(\DomainException::class, 'Daily cash closure already exists for 2026-07-04.');

    $this->assertDatabaseCount('daily_cash_closures', 1);
});

it('translates database unique violations during closure insert to the duplicate domain error', function (): void {
    createClosurePedido('PED-CLOSE-RACE', '2026-07-04', 'completado', 25.00, 'efectivo');

    DailyCashClosure::creating(function (DailyCashClosure $closure): void {
        DB::table('daily_cash_closures')->insert([
            'business_date' => $closure->business_date,
            'total_orders' => 0,
            'total_revenue' => 0,
            'settled_order_count' => 0,
            'pending_order_count' => 0,
            'cancelled_order_count' => 0,
            'payment_method_totals' => null,
            'closed_by_user_id' => null,
            'closed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    try {
        expect(fn (): DailyCashClosure => app(CloseDailyCashRegisterAction::class)->handle('2026-07-04', null))
            ->toThrow(\DomainException::class, 'Daily cash closure already exists for 2026-07-04.');
    } finally {
        DailyCashClosure::flushEventListeners();
    }
});

it('keeps stored closure totals as a snapshot when pedidos change later', function (): void {
    $pedido = createClosurePedido('PED-CLOSE-SNAP', '2026-07-04', 'completado', 25.00, 'efectivo');

    $closure = app(CloseDailyCashRegisterAction::class)->handle('2026-07-04', null);

    $pedido->update([
        'total' => 125.00,
    ]);

    expect((float) $closure->refresh()->total_revenue)->toBe(25.0)
        ->and($closure->payment_method_totals)->toBe([
            'efectivo' => [
                'count' => 1,
                'total' => '25.00',
            ],
        ]);
});

it('matches daily closure totals to completed order decimal totals exactly', function (): void {
    createClosurePedido('PED-CLOSE-DEC1', '2026-07-04', 'completado', 0.10, 'efectivo');
    createClosurePedido('PED-CLOSE-DEC2', '2026-07-04', 'completado', 0.20, 'efectivo');
    createClosurePedido('PED-CLOSE-DEC3', '2026-07-04', 'pendiente', 0.30, 'efectivo');

    $closure = app(CloseDailyCashRegisterAction::class)->handle('2026-07-04', null);

    expect($closure->total_revenue)->toBe('0.30')
        ->and($closure->payment_method_totals)->toBe([
            'efectivo' => [
                'count' => 2,
                'total' => '0.30',
            ],
        ]);
});

it('creates daily cash closure schema with duplicate prevention and reporting indexes', function (): void {
    expect(Schema::hasTable('daily_cash_closures'))->toBeTrue()
        ->and(Schema::hasColumns('daily_cash_closures', [
            'business_date',
            'total_orders',
            'total_revenue',
            'settled_order_count',
            'pending_order_count',
            'cancelled_order_count',
            'payment_method_totals',
            'closed_by_user_id',
            'closed_at',
        ]))->toBeTrue();
});

function createClosurePedido(
    string $numeroPedido,
    string $businessDate,
    string $estado,
    float $total,
    ?string $metodoPago,
): Pedido {
    $suffix = str_replace(['.', '-'], '', uniqid('', true));

    $cliente = Cliente::create([
        'nombre' => "Cash {$suffix}",
        'apellido' => 'Closure',
        'email' => "cash.closure.{$suffix}@example.com",
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => "Cashier {$suffix}",
        'rol_operativo' => 'cajero',
        'estado' => 'activo',
    ]);

    return Pedido::create([
        'numero_pedido' => $numeroPedido,
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => $metodoPago,
        'fecha' => $businessDate,
        'hora' => '08:30:00',
        'total' => $total,
        'estado' => $estado,
        'observaciones' => null,
    ]);
}
