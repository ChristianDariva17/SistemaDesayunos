<?php

declare(strict_types=1);

use App\Actions\Pedido\CreatePedidoAction;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

it('reserves products in canonical id order regardless of request order', function (): void {
    [$cliente, $empleado] = pedidoDeadlockEntities();

    $first = pedidoDeadlockProducto('First product', '1.25');
    $second = pedidoDeadlockProducto('Second product', '2.50');
    $third = pedidoDeadlockProducto('Third product', '3.75');

    $pedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $third->id, 'cantidad' => 1],
            ['id' => $first->id, 'cantidad' => 2],
            ['id' => $second->id, 'cantidad' => 3],
        ],
    ]);

    expect(StockMovimiento::query()
        ->where('pedido_id', $pedido->id)
        ->orderBy('id')
        ->pluck('producto_id')
        ->all())->toBe([$first->id, $second->id, $third->id])
        ->and($pedido->total)->toBe('13.75');
});

it('retries the complete pedido transaction after concurrency errors', function (): void {
    $attempts = 0;
    $creatingEvent = 'eloquent.creating: '.Pedido::class;

    // Laravel only retries a deadlock at the outermost transaction boundary.
    DB::rollBack();
    new Pedido;
    $originalListeners = Event::getFacadeRoot()->getRawListeners()[$creatingEvent] ?? [];

    try {
        [$cliente, $empleado] = pedidoDeadlockEntities();
        $producto = pedidoDeadlockProducto('Retry product', '4.25');

        Event::listen($creatingEvent, function () use (&$attempts): void {
            $attempts++;

            if ($attempts < 3) {
                throw new PDOException('Deadlock found when trying to get lock; try restarting transaction', 40001);
            }
        });

        $pedido = app(CreatePedidoAction::class)->handle([
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'productos' => [
                ['id' => $producto->id, 'cantidad' => 2],
            ],
        ]);

        expect($attempts)->toBe(3)
            ->and($pedido)->toBeInstanceOf(Pedido::class)
            ->and($producto->refresh()->stock)->toBe(8);

        $this->assertDatabaseCount('pedidos', 1);
        $this->assertDatabaseCount('pedido_producto', 1);
        $this->assertDatabaseCount('stock_movimientos', 1);
    } finally {
        Event::forget($creatingEvent);

        foreach ($originalListeners as $listener) {
            Event::listen($creatingEvent, $listener);
        }

        foreach ([
            'stock_movimientos',
            'stock_reservations',
            'pedido_producto',
            'pedidos',
            'producto_price_histories',
            'audits',
            'productos',
            'empleados',
        ] as $table) {
            DB::table($table)->delete();
        }

        DB::table('clientes')->where('id', $cliente->id)->delete();
        DB::beginTransaction();
    }
});

/**
 * @return array{Cliente, Empleado}
 */
function pedidoDeadlockEntities(): array
{
    $suffix = str_replace(['.', '-'], '', uniqid('', true));

    return [
        Cliente::create([
            'nombre' => 'Deadlock',
            'apellido' => 'Client',
            'email' => "deadlock.client.{$suffix}@example.com",
            'estado' => 'activo',
        ]),
        Empleado::create([
            'nombre' => "Deadlock Employee {$suffix}",
            'rol_operativo' => 'mesero',
            'estado' => 'activo',
        ]),
    ];
}

function pedidoDeadlockProducto(string $nombre, string $precio): Producto
{
    return Producto::create([
        'nombre' => $nombre,
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => $precio,
    ]);
}
