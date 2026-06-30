<?php

use App\Models\Producto;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Http\Response;

it('renders stock 10 in the admin low-stock report with the inclusive low-stock label', function (): void {
    $admin = User::factory()->create([
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Cafe',
        'categoria' => 'bebida',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 5.00,
    ]);

    Producto::create([
        'nombre' => 'Jugo',
        'categoria' => 'bebida',
        'stock' => 11,
        'estado' => 'activo',
        'precio' => 7.50,
    ]);

    $fakePdf = new class () extends DomPdfDocument {
        public string $renderedHtml = '';

        public function __construct()
        {
        }

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

    $response = $this->actingAs($admin)
        ->get(route('admin.reportes.stock-bajo', ['accion' => 'ver']));

    $response->assertOk();

    expect($fakePdf->renderedHtml)
        ->toContain('Cafe')
        ->toContain('<strong style="font-size: 12px; color: #dc2626;">10</strong>')
        ->toContain('BAJO')
        ->toContain('PRIORIDAD MEDIA')
        ->toContain('stock bajo (6-10 unidades)')
        ->toContain('Nivel de Stock Minimo:</strong> 10 unidades por producto.')
        ->not->toContain('Jugo');
});
