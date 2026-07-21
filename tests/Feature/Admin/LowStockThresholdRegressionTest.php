<?php

use App\Models\Producto;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Http\Response;

it('renders products at or below their configured minimum in the admin low-stock report', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 10,
        'stock_minimo' => 10,
        'estado' => 'activo',
        'precio' => 0.10,
    ]);

    Producto::create([
        'nombre' => 'Yerba',
        'categoria' => 'bebida',
        'stock' => 2,
        'stock_minimo' => 10,
        'estado' => 'activo',
        'precio' => 0.20,
    ]);

    Producto::create([
        'nombre' => 'Jugo',
        'categoria' => 'bebida',
        'stock' => 11,
        'stock_minimo' => 10,
        'estado' => 'activo',
        'precio' => 7.50,
    ]);

    Producto::create([
        'nombre' => 'Agua sin alerta',
        'categoria' => 'bebida',
        'stock' => 0,
        'stock_minimo' => 0,
        'estado' => 'activo',
        'precio' => 3.50,
    ]);

    $fakePdf = new class extends DomPdfDocument
    {
        public string $renderedHtml = '';

        public function __construct() {}

        public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): self
        {
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
    };

    Pdf::swap($fakePdf);

    $this->actingAs($admin)
        ->get(route('admin.reportes.inventario', ['accion' => 'ver']))
        ->assertOk();

    expect($fakePdf->renderedHtml)
        ->toContain('S/ 0.10')
        ->toContain('S/ 1.00')
        ->toContain('S/ 83.90');

    $response = $this->actingAs($admin)
        ->get(route('admin.reportes.stock-bajo', ['accion' => 'ver']));

    $response->assertOk();

    expect($fakePdf->renderedHtml)
        ->toContain('Cafe')
        ->toContain('Yerba')
        ->toContain('<strong style="font-size: 12px; color: #dc2626;">10</strong>')
        ->toContain('<strong>10</strong>')
        ->toContain('Faltante: 8 unid.')
        ->toContain('S/ 1.60')
        ->toContain('PRIORIDAD ALTA')
        ->toContain('EN MINIMO')
        ->toContain('PRIORIDAD MEDIA')
        ->toContain('Nivel de Stock Minimo:</strong> configurable por producto.')
        ->not->toContain('Jugo')
        ->not->toContain('Agua sin alerta');
});

it('requires an administrator to access the low-stock report', function (): void {
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $this->get(route('admin.reportes.stock-bajo'))
        ->assertRedirect(route('login', absolute: false));

    $this->actingAs($worker)
        ->get(route('admin.reportes.stock-bajo'))
        ->assertForbidden();
});
