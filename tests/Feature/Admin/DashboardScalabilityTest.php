<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Services\Reporting\DashboardSummaryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function dashboardScalabilityCliente(string $nombre = 'Dashboard Client'): Cliente
{
    return Cliente::create([
        'nombre' => $nombre,
        'apellido' => 'Reporter',
        'email' => fake()->unique()->safeEmail(),
        'estado' => 'activo',
    ]);
}

function dashboardScalabilityEmpleado(string $nombre = 'Dashboard Employee'): Empleado
{
    return Empleado::create([
        'nombre' => $nombre,
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
}

function dashboardScalabilityPedido(array $attributes = []): Pedido
{
    return Pedido::create(array_merge([
        'cliente_id' => dashboardScalabilityCliente()->id,
        'empleado_id' => dashboardScalabilityEmpleado()->id,
        'metodo_pago' => 'efectivo',
        'fecha' => now()->toDateString(),
        'hora' => '09:30:00',
        'impuesto' => 0,
        'total' => 25.50,
        'estado' => 'completado',
        'observaciones' => null,
    ], $attributes));
}

function dashboardScalabilityProduct(string $nombre = 'Dashboard Coffee'): Producto
{
    return Producto::create([
        'nombre' => $nombre,
        'categoria' => 'bebidas',
        'stock' => 10,
        'stock_minimo' => 5,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);
}

function dashboardScalabilityIndexNames(string $table): array
{
    if (DB::getDriverName() === 'sqlite') {
        return collect(DB::select("PRAGMA index_list('{$table}')"))
            ->pluck('name')
            ->all();
    }

    return collect(Schema::getIndexes($table))
        ->pluck('name')
        ->all();
}

beforeEach(function (): void {
    Cache::flush();
});

it('caches dashboard aggregate summaries with a short safe ttl', function (): void {
    dashboardScalabilityPedido(['total' => 10.00]);

    $service = app(DashboardSummaryService::class);

    $firstSummary = $service->summary();
    dashboardScalabilityPedido(['total' => 20.00]);
    $cachedSummary = $service->summary();

    expect($firstSummary['totalPedidos'])->toBe(1)
        ->and($cachedSummary['totalPedidos'])->toBe(1);

    Cache::forget('reporting.dashboard-summary.v1');

    expect($service->summary()['totalPedidos'])->toBe(2);
});

it('calculates monthly dashboard sales from creation timestamps', function (): void {
    $currentMonth = now()->startOfMonth()->addDays(2)->setTime(10, 0);
    $previousMonth = now()->subMonth()->startOfMonth()->addDays(2)->setTime(10, 0);

    dashboardScalabilityPedido([
        'fecha' => $previousMonth->toDateString(),
        'total' => 100.00,
    ])->forceFill([
        'created_at' => $currentMonth,
        'updated_at' => $currentMonth,
    ])->save();

    dashboardScalabilityPedido([
        'fecha' => $currentMonth->toDateString(),
        'total' => 50.00,
    ])->forceFill([
        'created_at' => $previousMonth,
        'updated_at' => $previousMonth,
    ])->save();

    $summary = app(DashboardSummaryService::class)->summary();

    expect((float) $summary['ventasMes'])->toBe(100.0);
});

it('renders the admin dashboard recent orders without lazy loading relationships', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $client = dashboardScalabilityCliente('Eager Loaded Client');
    dashboardScalabilityPedido(['cliente_id' => $client->id]);

    Model::preventLazyLoading(true);

    try {
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Eager Loaded Client');
    } finally {
        Model::preventLazyLoading(false);
    }
});

it('renders the worker dashboard recent orders without lazy loading relationships', function (): void {
    $worker = User::factory()->create(['rol' => 'trabajador']);
    $client = dashboardScalabilityCliente('Worker Eager Client');
    dashboardScalabilityPedido(['cliente_id' => $client->id]);

    Model::preventLazyLoading(true);

    try {
        $this->actingAs($worker)
            ->get(route('trabajador.dashboard'))
            ->assertOk()
            ->assertSee('Worker Eager Client');
    } finally {
        Model::preventLazyLoading(false);
    }
});

it('creates indexes for common dashboard and report filters', function (): void {
    expect(dashboardScalabilityIndexNames('pedidos'))
        ->toContain('pedidos_fecha_estado_index')
        ->toContain('pedidos_metodo_pago_fecha_index')
        ->and(dashboardScalabilityIndexNames('productos'))
        ->toContain('productos_categoria_index')
        ->and(dashboardScalabilityIndexNames('stock_movimientos'))
        ->toContain('stock_movimientos_tipo_created_at_index')
        ->toContain('stock_movimientos_user_created_at_index');
});

it('keeps top selling products in the cached dashboard summary', function (): void {
    $pedido = dashboardScalabilityPedido();
    $product = dashboardScalabilityProduct('Cached Bestseller');

    $pedido->productos()->attach($product->id, [
        'cantidad' => 3,
        'precio_unitario' => 12.50,
        'subtotal' => 37.50,
    ]);

    $summary = app(DashboardSummaryService::class)->summary();

    expect($summary['productosMasVendidos']->first()->nombre)->toBe('Cached Bestseller')
        ->and((int) $summary['productosMasVendidos']->first()->total_vendido)->toBe(3);
});
