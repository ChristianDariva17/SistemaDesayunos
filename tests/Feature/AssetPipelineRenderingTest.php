<?php

declare(strict_types=1);

use App\Models\User;

it('defers legacy admin page scripts until Vite exposes browser globals', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-legacy-assets@example.test',
        'rol' => 'administrador',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.empleados.create'));

    $content = $response->assertOk()->getContent();

    expect($content)->toContain('type="module"')
        ->and($content)->toContain('<template data-run-after-vite="legacy-scripts">')
        ->and($content)->toContain('$(document).ready(function()')
        ->and(strpos($content, '<template data-run-after-vite="legacy-scripts">'))->toBeLessThan(
            strpos($content, '$(document).ready(function()')
        );
});

it('keeps Vite globals and Bootstrap jQuery bridges before legacy scripts are executed', function (): void {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)->toContain('window.bootstrap = bootstrap;')
        ->and($script)->toContain('window.$ = window.jQuery = $;')
        ->and($script)->toContain('window.Swal = Swal;')
        ->and($script)->toContain('$.fn.modal')
        ->and($script)->toContain('$.fn.tooltip')
        ->and(strpos($script, 'window.$ = window.jQuery = $;'))->toBeLessThan(strpos($script, 'runLegacyPageScripts();'))
        ->and(strpos($script, 'window.Swal = Swal;'))->toBeLessThan(strpos($script, 'runLegacyPageScripts();'))
        ->and(strpos($script, 'window.bootstrap = bootstrap;'))->toBeLessThan(strpos($script, 'runLegacyPageScripts();'));
});

it('does not define Tailwind components that override Bootstrap class names globally', function (): void {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->not->toContain('.btn-primary {')
        ->and($css)->not->toContain('.btn-secondary {')
        ->and($css)->not->toContain('.btn-success {')
        ->and($css)->not->toContain('.card {')
        ->and($css)->not->toContain('.form-input {')
        ->and($css)->not->toContain('.form-select {')
        ->and($css)->not->toContain('.form-textarea {')
        ->and($css)->toContain('.app-btn-primary')
        ->and($css)->toContain('.app-card');
});

it('keeps Chart.js and report chart assets out of pages without rendered chart hooks', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-report-chart-assets@example.test',
        'rol' => 'administrador',
    ]);

    $appScript = file_get_contents(resource_path('js/app.js'));
    $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
    $workerLayout = file_get_contents(resource_path('views/layouts/trabajador.blade.php'));

    expect($appScript)->not->toContain('chart.js')
        ->and($appLayout)->not->toContain('cdn.jsdelivr.net/npm/chart.js')
        ->and($workerLayout)->not->toContain('cdn.jsdelivr.net/npm/chart.js');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('/build/assets/report-charts-', false)
        ->assertDontSee('cdn.jsdelivr.net/npm/chart.js', false);

    $this->actingAs($admin)
        ->get(route('admin.reportes.index'))
        ->assertOk()
        ->assertDontSee('/build/assets/report-charts-', false)
        ->assertDontSee('data-chartjs', false)
        ->assertDontSee('data-chartjs-config', false)
        ->assertSee('Centro de Reportes')
        ->assertSee('Análisis de Ventas')
        ->assertSee('id="formVentas"', false)
        ->assertDontSee('cdn.jsdelivr.net/npm/chart.js', false);
});
