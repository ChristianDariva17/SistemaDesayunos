<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

final class SalesByClientPdfFake extends DomPdfDocument
{
    /** @var array<string, mixed> */
    public array $viewData = [];

    public string $renderedHtml = '';

    public function __construct() {}

    public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): self
    {
        $this->viewData = $data;
        $this->renderedHtml = view($view, $data, $mergeData)->render();

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

it('renders canonical grouped and grand totals for a populated range', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $empleado = Empleado::create([
        'user_id' => $admin->id,
        'nombre' => 'Admin Reportes',
        'rol_operativo' => 'admin',
        'estado' => 'activo',
    ]);
    $ana = Cliente::create(['nombre' => 'Ana Decimal', 'email' => 'ana@example.test', 'estado' => 'activo']);
    $beto = Cliente::create(['nombre' => 'Beto Decimal', 'telefono' => '999 111 222', 'estado' => 'activo']);

    foreach ([[$ana->id, '0.10'], [$ana->id, '0.20'], [$beto->id, '0.30']] as [$clienteId, $total]) {
        Pedido::create([
            'cliente_id' => $clienteId,
            'empleado_id' => $empleado->id,
            'fecha' => '2026-07-10',
            'hora' => '10:00:00',
            'total' => $total,
            'estado' => 'completado',
        ]);
    }

    $pdf = new SalesByClientPdfFake;
    Pdf::swap($pdf);

    actingAs($admin);
    get(route('admin.reportes.ventas-por-cliente', [
        'accion' => 'ver',
        'fecha_inicio' => '2026-07-01',
        'fecha_fin' => '2026-07-31',
    ]))->assertOk()->assertHeader('Content-Type', 'application/pdf');

    expect($pdf->viewData['ventasPorCliente']->pluck('total_ventas')->all())
        ->toBe(['0.30', '0.30'])
        ->and($pdf->viewData['ventasGenerales'])->toBe('0.60')
        ->and($pdf->renderedHtml)->toContain('Ana Decimal')
        ->toContain('Beto Decimal')
        ->toContain('S/ 0.30')
        ->toContain('S/ 0.60')
        ->and(str_contains($pdf->renderedHtml, 'View [admin.reportes.ventas-por-cliente] not found'))->toBeFalse();
});

it('renders canonical zero totals and an empty state for a range without sales', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $pdf = new SalesByClientPdfFake;
    Pdf::swap($pdf);

    actingAs($admin);
    get(route('admin.reportes.ventas-por-cliente', [
        'accion' => 'ver',
        'fecha_inicio' => '2026-07-01',
        'fecha_fin' => '2026-07-31',
    ]))->assertOk();

    expect($pdf->viewData['totalClientes'])->toBe(0)
        ->and($pdf->viewData['ventasGenerales'])->toBe('0.00')
        ->and($pdf->renderedHtml)->toContain('S/ 0.00')
        ->toContain('No se registraron ventas en el período seleccionado.');
});

it('preserves administrator middleware for the sales-by-client report', function (): void {
    $worker = User::factory()->create(['rol' => 'trabajador']);

    get(route('admin.reportes.ventas-por-cliente'))
        ->assertRedirect(route('login', absolute: false));

    actingAs($worker);
    get(route('admin.reportes.ventas-por-cliente'))
        ->assertForbidden();
});
