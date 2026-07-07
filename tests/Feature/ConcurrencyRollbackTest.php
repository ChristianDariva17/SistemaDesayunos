<?php

declare(strict_types=1);

use App\Actions\Cash\CloseDailyCashRegisterAction;
use App\Actions\Pedido\CreatePedidoAction;
use App\Actions\Pedido\UpdatePedidoAction;
use App\Actions\Stock\RegisterStockMovementAction;
use App\Models\Audit;
use App\Models\Cliente;
use App\Models\DailyCashClosure;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

it('prevents simultaneous pedido creations from overselling stock with separate processes', function (): void {
    $databasePath = database_path('testing-concurrent-pedidos.sqlite');
    $barrierPath = storage_path('framework/testing-concurrent-pedidos-go');

    concurrencyRollbackPrepareSqliteDatabase($databasePath);

    try {
        [$clienteId, $empleadoId, $productoId] = concurrencyRollbackSeedSqlitePedidoFixture($databasePath, stock: 5);

        $first = concurrencyRollbackPedidoWorker($databasePath, $barrierPath, $clienteId, $empleadoId, $productoId);
        $second = concurrencyRollbackPedidoWorker($databasePath, $barrierPath, $clienteId, $empleadoId, $productoId);

        $first->start();
        $second->start();

        file_put_contents($barrierPath, 'go');

        $first->wait();
        $second->wait();

        $results = [
            concurrencyRollbackDecodeWorkerOutput($first),
            concurrencyRollbackDecodeWorkerOutput($second),
        ];
        $errorResult = collect($results)->firstWhere('status', 'error');

        expect(collect($results)->where('status', 'ok')->count())->toBe(1)
            ->and(collect($results)->where('status', 'error')->count())->toBe(1)
            ->and($errorResult)->not->toBeNull();

        concurrencyRollbackExpectExpectedWorkerError($errorResult);

        expect(concurrencyRollbackSqliteScalar($databasePath, 'select stock from productos where id = '.$productoId))->toBe(2)
            ->and(concurrencyRollbackSqliteScalar($databasePath, 'select count(*) from pedidos'))->toBe(1)
            ->and(concurrencyRollbackSqliteScalar($databasePath, 'select count(*) from pedido_producto'))->toBe(1)
            ->and(concurrencyRollbackSqliteScalar($databasePath, 'select count(*) from stock_movimientos'))->toBe(1);

    } finally {
        if (file_exists($barrierPath)) {
            unlink($barrierPath);
        }

        foreach ([$databasePath, $databasePath.'-shm', $databasePath.'-wal'] as $sqliteFile) {
            if (! file_exists($sqliteFile)) {
                continue;
            }

            unlink($sqliteFile);
        }
    }
});

it('prevents sequential pedido creations from overselling stock under deterministic pressure', function (): void {
    [$cliente, $empleado, $producto] = concurrencyRollbackPedidoFixture(stock: 5);

    $firstPedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 3],
        ],
    ]);

    expect(fn (): Pedido => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 3],
        ],
    ]))->toThrow(Exception::class, 'Stock insuficiente para Concurrency Sandwich. Disponible: 2');

    expect($firstPedido->refresh()->productos)->toHaveCount(1)
        ->and($producto->refresh()->stock)->toBe(2)
        ->and($producto->availableStock())->toBe(2);

    $this->assertDatabaseCount('pedidos', 1);
    $this->assertDatabaseCount('pedido_producto', 1);
    $this->assertDatabaseCount('stock_movimientos', 1);
});

it('treats active reservations as unavailable during creation and reactivation', function (): void {
    [$cliente, $empleado, $producto] = concurrencyRollbackPedidoFixture(stock: 5);
    $reservedPedido = concurrencyRollbackRawPedido($cliente, $empleado, 'PED-CONC-RSV1');
    StockReservation::reserve($producto, $reservedPedido, 4);

    expect(fn (): Pedido => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 2],
        ],
    ]))->toThrow(Exception::class, 'Stock insuficiente para Concurrency Sandwich. Disponible: 1');

    $cancelledPedido = concurrencyRollbackRawPedido($cliente, $empleado, 'PED-CONC-REAC', 'cancelado');
    $cancelledPedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => '4.25',
        'subtotal' => '8.50',
    ]);

    expect(fn (): Pedido => app(UpdatePedidoAction::class)->handle([
        'estado' => 'pendiente',
        'observaciones' => 'Reactivate under reservation pressure',
    ], $cancelledPedido))->toThrow(Exception::class, 'Stock insuficiente para Concurrency Sandwich');

    $this->assertDatabaseHas('pedidos', [
        'id' => $cancelledPedido->id,
        'estado' => 'cancelado',
        'observaciones' => null,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($producto->refresh()->stock)->toBe(5)
        ->and($producto->availableStock())->toBe(1);
});

it('keeps reservation release consume and cancel operations idempotent', function (): void {
    [$cliente, $empleado, $producto] = concurrencyRollbackPedidoFixture(stock: 9);
    $registerStockMovement = app(RegisterStockMovementAction::class);

    $released = StockReservation::reserve($producto, concurrencyRollbackRawPedido($cliente, $empleado, 'PED-CONC-REL'), 2)->release();
    $cancelled = StockReservation::reserve($producto, concurrencyRollbackRawPedido($cliente, $empleado, 'PED-CONC-CAN'), 3)->cancel();
    $consumed = StockReservation::reserve($producto, concurrencyRollbackRawPedido($cliente, $empleado, 'PED-CONC-CON'), 4)
        ->consume($registerStockMovement);

    $released->release()->cancel()->consume($registerStockMovement);
    $cancelled->cancel()->release()->consume($registerStockMovement);
    $consumed->consume($registerStockMovement)->release()->cancel();

    expect($released->refresh()->status)->toBe(StockReservation::STATUS_RELEASED)
        ->and($cancelled->refresh()->status)->toBe(StockReservation::STATUS_CANCELLED)
        ->and($consumed->refresh()->status)->toBe(StockReservation::STATUS_CONSUMED)
        ->and($producto->refresh()->stock)->toBe(5)
        ->and($producto->activeReservedStock())->toBe(0);

    $this->assertDatabaseCount('stock_movimientos', 1);
});

it('returns a domain error when a duplicate daily closure appears during insert', function (): void {
    concurrencyRollbackClosurePedido('PED-CONC-CASH', '2026-07-04', 'completado', '25.00', 'efectivo');

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
            ->toThrow(DomainException::class, 'Daily cash closure already exists for 2026-07-04.');
    } finally {
        DailyCashClosure::flushEventListeners();
    }
});

it('rolls back stock movements pivot rows and audits when pedido creation fails halfway', function (): void {
    [$cliente, $empleado, $productoConStock] = concurrencyRollbackPedidoFixture(stock: 5);
    $productoSinStock = Producto::create([
        'nombre' => 'Concurrency Coffee',
        'categoria' => 'bebida',
        'stock' => 1,
        'estado' => 'activo',
        'precio' => '2.50',
    ]);
    $auditCount = Audit::query()->count();

    expect(fn (): Pedido => app(CreatePedidoAction::class)->handle([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'observaciones' => 'Rollback halfway pedido',
        'productos' => [
            ['id' => $productoConStock->id, 'cantidad' => 2],
            ['id' => $productoSinStock->id, 'cantidad' => 2],
        ],
    ]))->toThrow(Exception::class, 'Stock insuficiente para Concurrency Coffee. Disponible: 1');

    $this->assertDatabaseMissing('pedidos', [
        'observaciones' => 'Rollback halfway pedido',
    ]);
    $this->assertDatabaseMissing('pedido_producto', [
        'producto_id' => $productoConStock->id,
        'cantidad' => 2,
    ]);
    $this->assertDatabaseCount('stock_movimientos', 0);

    expect($productoConStock->refresh()->stock)->toBe(5)
        ->and($productoSinStock->refresh()->stock)->toBe(1)
        ->and(Audit::query()->count())->toBe($auditCount);
});

it('keeps pedido creation consistent when audit writes fail', function (): void {
    [$cliente, $empleado, $producto] = concurrencyRollbackPedidoFixture(stock: 4);

    Audit::creating(function (): void {
        throw new RuntimeException('Forced audit storage failure.');
    });

    try {
        $pedido = app(CreatePedidoAction::class)->handle([
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'productos' => [
                ['id' => $producto->id, 'cantidad' => 2],
            ],
        ]);

        expect($pedido->refresh()->productos)->toHaveCount(1)
            ->and($producto->refresh()->stock)->toBe(2);

        $this->assertDatabaseHas('stock_movimientos', [
            'producto_id' => $producto->id,
            'pedido_id' => $pedido->id,
            'tipo' => StockMovimiento::TIPO_SALIDA,
            'cantidad' => 2,
            'stock_anterior' => 4,
            'stock_nuevo' => 2,
        ]);
    } finally {
        Audit::flushEventListeners();
    }
});

function concurrencyRollbackPedidoFixture(int $stock): array
{
    $suffix = str_replace(['.', '-'], '', uniqid('', true));

    $cliente = Cliente::create([
        'nombre' => "Concurrency {$suffix}",
        'apellido' => 'Client',
        'email' => "concurrency.client.{$suffix}@example.com",
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => "Concurrency Employee {$suffix}",
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Concurrency Sandwich',
        'categoria' => 'desayuno',
        'stock' => $stock,
        'estado' => 'activo',
        'precio' => '4.25',
    ]);

    return [$cliente, $empleado, $producto];
}

function concurrencyRollbackPrepareSqliteDatabase(string $databasePath): void
{
    if (file_exists($databasePath)) {
        unlink($databasePath);
    }

    touch($databasePath);

    $migration = new Process(
        [PHP_BINARY, 'artisan', 'migrate:fresh', '--force'],
        base_path(),
        [
            'APP_ENV' => 'testing',
            'CACHE_STORE' => 'array',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $databasePath,
        ],
        null,
        30,
    );

    $migration->mustRun();

    $pdo = concurrencyRollbackSqlitePdo($databasePath);
    $pdo->exec('PRAGMA busy_timeout = 10000');
    $pdo->exec('PRAGMA journal_mode = WAL');
}

function concurrencyRollbackSeedSqlitePedidoFixture(string $databasePath, int $stock): array
{
    $pdo = concurrencyRollbackSqlitePdo($databasePath);
    $now = now()->toDateTimeString();
    $suffix = str_replace(['.', '-'], '', uniqid('', true));

    $pdo->prepare('insert into clientes (nombre, apellido, email, estado, created_at, updated_at) values (?, ?, ?, ?, ?, ?)')
        ->execute(["Concurrency {$suffix}", 'Client', "concurrency.client.{$suffix}@example.com", 'activo', $now, $now]);
    $clienteId = (int) $pdo->lastInsertId();

    $pdo->prepare('insert into empleados (nombre, rol_operativo, estado, created_at, updated_at) values (?, ?, ?, ?, ?)')
        ->execute(["Concurrency Employee {$suffix}", 'mesero', 'activo', $now, $now]);
    $empleadoId = (int) $pdo->lastInsertId();

    $pdo->prepare('insert into productos (nombre, categoria, stock, estado, precio, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?)')
        ->execute(['Concurrency Sandwich', 'desayuno', $stock, 'activo', '4.25', $now, $now]);
    $productoId = (int) $pdo->lastInsertId();

    $pdo->prepare('insert into producto_price_histories (producto_id, precio, effective_from, effective_to, created_at, updated_at) values (?, ?, ?, null, ?, ?)')
        ->execute([$productoId, '4.25', $now, $now, $now]);

    return [$clienteId, $empleadoId, $productoId];
}

function concurrencyRollbackSqlitePdo(string $databasePath): PDO
{
    $pdo = new PDO('sqlite:'.$databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function concurrencyRollbackSqliteScalar(string $databasePath, string $sql): int
{
    return (int) concurrencyRollbackSqlitePdo($databasePath)->query($sql)->fetchColumn();
}

function concurrencyRollbackExpectExpectedWorkerError(array $result): void
{
    $class = $result['class'] ?? null;
    $message = (string) ($result['message'] ?? '');
    $normalizedMessage = strtolower($message);

    $isExpectedStockError = $class === Exception::class
        && str_contains($message, 'Stock insuficiente para Concurrency Sandwich. Disponible: 2');

    $isExpectedSqliteLockError = in_array($class, [
        \Illuminate\Database\QueryException::class,
        PDOException::class,
    ], true) && (
        str_contains($normalizedMessage, 'database is locked')
        || str_contains($normalizedMessage, 'database table is locked')
    );

    \PHPUnit\Framework\Assert::assertTrue(
        $isExpectedStockError || $isExpectedSqliteLockError,
        sprintf('Unexpected concurrency worker error [%s]: %s', (string) $class, $message),
    );
}

function concurrencyRollbackPedidoWorker(
    string $databasePath,
    string $barrierPath,
    int $clienteId,
    int $empleadoId,
    int $productoId,
): Process {
    $code = <<<'PHP'
use App\Actions\Pedido\CreatePedidoAction;
use Illuminate\Support\Facades\DB;

$databasePath = getenv('CONCURRENCY_DB_DATABASE');
$barrierPath = getenv('CONCURRENCY_BARRIER_PATH');

config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => $databasePath,
    'database.connections.sqlite.busy_timeout' => 10000,
    'database.connections.sqlite.journal_mode' => 'WAL',
]);

DB::purge('sqlite');
DB::reconnect('sqlite');
DB::statement('PRAGMA busy_timeout = 10000');
DB::statement('PRAGMA journal_mode = WAL');

$deadline = microtime(true) + 10;
while (! file_exists($barrierPath)) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Timed out waiting for concurrency barrier.');
    }

    usleep(10000);
}

try {
    $pedido = app(CreatePedidoAction::class)->handle([
        'cliente_id' => (int) getenv('CONCURRENCY_CLIENTE_ID'),
        'empleado_id' => (int) getenv('CONCURRENCY_EMPLEADO_ID'),
        'metodo_pago' => 'efectivo',
        'productos' => [
            ['id' => (int) getenv('CONCURRENCY_PRODUCTO_ID'), 'cantidad' => 3],
        ],
    ]);

    echo json_encode(['status' => 'ok', 'pedido_id' => $pedido->id], JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    echo json_encode([
        'status' => 'error',
        'class' => $exception::class,
        'message' => $exception->getMessage(),
    ], JSON_THROW_ON_ERROR);
}
PHP;

    return new Process(
        [PHP_BINARY, 'artisan', 'tinker', '--execute', $code],
        base_path(),
        [
            'APP_ENV' => 'testing',
            'CACHE_STORE' => 'array',
            'CONCURRENCY_DB_DATABASE' => $databasePath,
            'CONCURRENCY_BARRIER_PATH' => $barrierPath,
            'CONCURRENCY_CLIENTE_ID' => (string) $clienteId,
            'CONCURRENCY_EMPLEADO_ID' => (string) $empleadoId,
            'CONCURRENCY_PRODUCTO_ID' => (string) $productoId,
        ],
        null,
        30,
    );
}

function concurrencyRollbackDecodeWorkerOutput(Process $process): array
{
    $output = trim($process->getOutput());
    $decoded = json_decode($output, true);

    if (! is_array($decoded)) {
        throw new RuntimeException(sprintf(
            'Concurrency worker failed with exit code %s. Output: %s Error: %s',
            (string) $process->getExitCode(),
            $output,
            trim($process->getErrorOutput()),
        ));
    }

    return $decoded;
}

function concurrencyRollbackRawPedido(
    Cliente $cliente,
    Empleado $empleado,
    string $numeroPedido,
    string $estado = 'pendiente',
): Pedido {
    return Pedido::create([
        'numero_pedido' => $numeroPedido,
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-04',
        'hora' => '08:30:00',
        'total' => 0,
        'estado' => $estado,
        'observaciones' => null,
    ]);
}

function concurrencyRollbackClosurePedido(
    string $numeroPedido,
    string $businessDate,
    string $estado,
    string $total,
    ?string $metodoPago,
): Pedido {
    [$cliente, $empleado] = concurrencyRollbackPedidoFixture(stock: 1);

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
