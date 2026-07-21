<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

final class BoundedReportPdfFake extends DomPdfDocument
{
    public function __construct() {}

    public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): self
    {
        return $this;
    }

    public function setPaper($paper, string $orientation = 'portrait'): self
    {
        return $this;
    }

    public function setOption($attribute, $value = null): self
    {
        return $this;
    }

    public function stream(string $filename = 'document.pdf'): Response
    {
        return response('', 200, ['Content-Type' => 'application/pdf']);
    }

    public function download(string $filename = 'document.pdf'): Response
    {
        return response('', 200, ['Content-Type' => 'application/pdf']);
    }
}

function createBoundedReportProducts(int $count, bool $lowStock): void
{
    foreach (range(1, $count) as $index) {
        Producto::create([
            'nombre' => "Producto {$index}",
            'categoria' => 'prueba',
            'stock' => $lowStock ? 1 : 20,
            'stock_minimo' => 10,
            'estado' => 'activo',
            'precio' => '1.00',
        ]);
    }
}

function createBoundedReportPedidos(User $admin, int $count, string $fecha = '2026-07-10'): void
{
    $cliente = Cliente::create([
        'nombre' => 'Cliente límite',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'user_id' => $admin->id,
        'nombre' => 'Admin límite',
        'rol_operativo' => 'admin',
        'estado' => 'activo',
    ]);

    foreach (range(1, $count) as $index) {
        Pedido::create([
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'fecha' => $fecha,
            'hora' => sprintf('10:%02d:00', $index),
            'total' => '1.00',
            'estado' => 'completado',
        ]);
    }
}

beforeEach(function (): void {
    config()->set('reportes.pdf_sync_max_rows', 2);
    config()->set('reportes.pdf_sync_max_days', 3);
    Queue::fake();
});

it('rejects invalid actions without loading DomPDF or dispatching jobs', function (string $routeName): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    Pdf::shouldReceive('loadView')->never();

    actingAs($admin);
    get(route($routeName, ['accion' => 'imprimir']), [
        'referer' => route('admin.reportes.index'),
    ])
        ->assertRedirect(route('admin.reportes.index'))
        ->assertSessionHasErrors([
            'accion' => 'La acción seleccionada no es válida. Usa ver o descargar.',
        ]);

    Queue::assertNothingPushed();
})->with([
    'inventory' => ['admin.reportes.inventario'],
    'low stock' => ['admin.reportes.stock-bajo'],
    'sales' => ['admin.reportes.ventas'],
    'sales by client' => ['admin.reportes.ventas-por-cliente'],
]);

it('rejects malformed, reversed, and over-limit sales dates before DomPDF', function (
    string $routeName,
    array $parameters,
    string $field,
    string $message,
): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    Pdf::shouldReceive('loadView')->never();

    actingAs($admin);
    get(route($routeName, ['accion' => 'ver'] + $parameters), [
        'referer' => route('admin.reportes.index'),
    ])
        ->assertRedirect(route('admin.reportes.index'))
        ->assertSessionHasErrors([$field => $message]);

    Queue::assertNothingPushed();
})->with([
    'sales malformed start' => [
        'admin.reportes.ventas',
        ['fecha_inicio' => '10-07-2026', 'fecha_fin' => '2026-07-10'],
        'fecha_inicio',
        'La fecha inicial debe tener el formato AAAA-MM-DD.',
    ],
    'sales by client malformed end' => [
        'admin.reportes.ventas-por-cliente',
        ['fecha_inicio' => '2026-07-10', 'fecha_fin' => '11-07-2026'],
        'fecha_fin',
        'La fecha final debe tener el formato AAAA-MM-DD.',
    ],
    'sales reversed' => [
        'admin.reportes.ventas',
        ['fecha_inicio' => '2026-07-11', 'fecha_fin' => '2026-07-10'],
        'fecha_fin',
        'La fecha final debe ser igual o posterior a la fecha inicial.',
    ],
    'sales by client over day limit' => [
        'admin.reportes.ventas-por-cliente',
        ['fecha_inicio' => '2026-07-10', 'fecha_fin' => '2026-07-13'],
        'fecha_fin',
        'El rango de fechas no puede superar 3 días. Reduce el período seleccionado.',
    ],
]);

it('allows each report exactly at its configured row limit', function (string $routeName, string $source): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    if ($source === 'inventory') {
        createBoundedReportProducts(2, false);
    } elseif ($source === 'low-stock') {
        createBoundedReportProducts(2, true);
    } else {
        createBoundedReportPedidos($admin, 2);
    }

    Pdf::swap(new BoundedReportPdfFake);

    actingAs($admin);
    get(route($routeName, [
        'accion' => 'ver',
        'fecha_inicio' => '2026-07-09',
        'fecha_fin' => '2026-07-11',
    ]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    Queue::assertNothingPushed();
})->with([
    'inventory' => ['admin.reportes.inventario', 'inventory'],
    'low stock' => ['admin.reportes.stock-bajo', 'low-stock'],
    'sales' => ['admin.reportes.ventas', 'pedidos'],
    'sales by client uses source pedidos' => ['admin.reportes.ventas-por-cliente', 'pedidos'],
]);

it('rejects each report above its configured source row limit', function (
    string $routeName,
    string $source,
    string $reportName,
): void {
    $admin = User::factory()->create(['rol' => 'administrador']);

    if ($source === 'inventory') {
        createBoundedReportProducts(3, false);
    } elseif ($source === 'low-stock') {
        createBoundedReportProducts(3, true);
    } else {
        createBoundedReportPedidos($admin, 3);
        expect(Pedido::query()->whereBetween('fecha', ['2026-07-09', '2026-07-11'])->count())->toBe(3);
    }

    Pdf::shouldReceive('loadView')->never();

    actingAs($admin);
    get(route($routeName, [
        'accion' => 'ver',
        'fecha_inicio' => '2026-07-09',
        'fecha_fin' => '2026-07-11',
    ]), [
        'referer' => route('admin.reportes.index'),
    ])
        ->assertRedirect(route('admin.reportes.index'))
        ->assertSessionHasErrors([
            'reporte' => "El reporte de {$reportName} supera el límite de 2 registros. Reduce el período o los datos e inténtalo nuevamente.",
        ]);

    Queue::assertNothingPushed();
})->with([
    'inventory' => ['admin.reportes.inventario', 'inventory', 'inventario'],
    'low stock' => ['admin.reportes.stock-bajo', 'low-stock', 'stock bajo'],
    'sales' => ['admin.reportes.ventas', 'pedidos', 'ventas'],
    'sales by client counts source pedidos' => ['admin.reportes.ventas-por-cliente', 'pedidos', 'ventas por cliente'],
]);
