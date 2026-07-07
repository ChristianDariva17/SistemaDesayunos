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
