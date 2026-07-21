<?php

declare(strict_types=1);

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('keeps admin PDF failures internal', function (string $routeName, string $viewName, string $reportName): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $failure = new Error("Unique report failure for {$reportName}: SELECT secret FROM private_table");

    $this->assertNotSame('', $failure->getTraceAsString());

    Pdf::shouldReceive('loadView')
        ->once()
        ->with($viewName, Mockery::type('array'))
        ->andThrow($failure);

    Log::shouldReceive('error')
        ->once()
        ->with('Admin PDF report generation failed.', Mockery::on(
            fn (array $context): bool => $context['operation_name'] === $routeName
                && $context['report_name'] === $reportName
                && $context['user_id'] === $admin->id
                && $context['exception'] === $failure,
        ));

    actingAs($admin);
    $response = get(route($routeName), [
        'referer' => route('admin.reportes.index'),
    ]);

    $response
        ->assertRedirect(route('admin.reportes.index'))
        ->assertSessionHas('error', 'Error al generar el reporte. Por favor intenta nuevamente.');

    $feedback = session()->get('error');
    $this->assertIsString($feedback);

    $this->assertStringNotContainsString($failure->getMessage(), $feedback);
    $this->assertStringNotContainsString($failure->getFile(), $feedback);
    $this->assertStringNotContainsString((string) $failure->getLine(), $feedback);
    $this->assertStringNotContainsString($failure->getTraceAsString(), $feedback);
})->with([
    'inventory report' => ['admin.reportes.inventario', 'admin.reportes.inventario', 'inventario'],
    'low-stock report' => ['admin.reportes.stock-bajo', 'admin.reportes.stock-bajo', 'stock-bajo'],
    'sales report' => ['admin.reportes.ventas', 'admin.reportes.ventas', 'ventas'],
    'sales-by-client report' => ['admin.reportes.ventas-por-cliente', 'admin.reportes.ventas-por-cliente', 'ventas-por-cliente'],
]);
