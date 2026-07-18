<?php

declare(strict_types=1);

use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
});

it('renders admin dashboard component values, alert semantics, and empty states', function (): void {
    $admin = User::factory()->create([
        'name' => 'Admin Component Tester',
        'email' => 'admin-components@example.test',
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Café pasado',
        'descripcion' => 'Bebida caliente',
        'categoria' => 'bebidas',
        'precio' => 4.50,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    Producto::create([
        'nombre' => 'Pan con pollo',
        'descripcion' => 'Desayuno clásico',
        'categoria' => 'comidas',
        'precio' => 8.00,
        'stock' => 12,
        'estado' => 'inactivo',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    expect($response->getContent())->toMatch(
        '/class="(?=[^"]*\\btext-primary\\b)(?=[^"]*\\btext-uppercase\\b)(?=[^"]*\\bmb-2\\b)[^"]*"\s*>\s*<i class="fas fa-box me-1"[^>]*><\/i>Total Productos/s'
    );

    $response->assertOk()
        ->assertSee('role="alert"', false)
        ->assertSee('¡Bienvenido, Admin Component Tester!')
        ->assertSee('alert-info', false)
        ->assertSee('btn-close', false)
        ->assertSee('aria-label="Close"', false)
        ->assertSee('Total Productos')
        ->assertSee('2')
        ->assertSee('Activos: 1')
        ->assertSee('href="'.route('admin.productos.index').'"', false)
        ->assertSee('Stock Bajo')
        ->assertSee('Productos críticos')
        ->assertSee('href="'.route('admin.productos.index').'?stock=bajo"', false)
        ->assertSee('Ver productos')
        ->assertSee('Total Ventas')
        ->assertSee('S/ 0.00')
        ->assertSee('No hay datos de ventas disponibles')
        ->assertSee('No hay pedidos recientes')
        ->assertSee('aria-hidden="true"', false);

    assertPendingOrderCardTarget($response->getContent(), route('admin.pedidos.index', ['estado' => 'pendiente']));
    assertAdminPendingOrderNotificationTarget($response->getContent(), route('admin.pedidos.index', ['estado' => 'pendiente']));
});

it('renders worker dashboard component values with worker routes and empty states', function (): void {
    $worker = User::factory()->create([
        'name' => 'Worker Component Tester',
        'email' => 'worker-components@example.test',
        'rol' => 'trabajador',
    ]);

    Producto::create([
        'nombre' => 'Jugo surtido',
        'descripcion' => 'Bebida fría',
        'categoria' => 'bebidas',
        'precio' => 6.50,
        'stock' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($worker)
        ->get(route('trabajador.dashboard'));

    expect($response->getContent())->toMatch(
        '/class="(?=[^"]*\\btext-primary\\b)(?![^"]*\\btext-uppercase\\b)(?=[^"]*\\bmb-2\\b)[^"]*"\s*>\s*<i class="fas fa-box me-1"[^>]*><\/i>Total Productos/s'
    );

    $response->assertOk()
        ->assertSee('role="alert"', false)
        ->assertSee('¡Bienvenido, Worker Component Tester!')
        ->assertSee('Total Productos')
        ->assertSee('Activos: 1')
        ->assertSee('href="'.route('trabajador.productos.index').'"', false)
        ->assertSee('href="'.route('trabajador.productos.index').'?stock=bajo"', false)
        ->assertSee('Ver productos')
        ->assertSee('Total Ventas')
        ->assertSee('S/ 0.00')
        ->assertSee('No hay datos de ventas disponibles')
        ->assertSee('No hay pedidos recientes');

    assertPendingOrderCardTarget($response->getContent(), route('trabajador.pedidos.index', ['estado' => 'pendiente']));
});

it('renders product listing action targets, form methods, alerts, and empty state behavior', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-product-actions@example.test',
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Tamales verdes',
        'descripcion' => 'Producto de desayuno',
        'categoria' => 'comidas',
        'precio' => 9.50,
        'sku' => 'TAM-001',
        'stock' => 3,
        'stock_minimo' => 5,
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['success' => 'Producto guardado correctamente.'])
        ->get(route('admin.productos.index'));

    $response->assertOk()
        ->assertSee('role="alert"', false)
        ->assertSee('¡Éxito!')
        ->assertSee('Producto guardado correctamente.')
        ->assertSee('role="group" aria-label="Acciones de producto"', false)
        ->assertSee('href="'.route('admin.productos.show', $producto).'"', false)
        ->assertSee('title="Ver detalles"', false)
        ->assertSee('href="'.route('admin.productos.edit', $producto).'"', false)
        ->assertSee('title="Editar"', false)
        ->assertSee('data-bs-target="#productStockModal"', false)
        ->assertSee('data-product-stock-action="'.route('admin.productos.actualizar-stock', $producto).'"', false)
        ->assertSee('name="_method" value="PATCH"', false)
        ->assertSee('action="'.route('admin.productos.duplicar', $producto).'"', false)
        ->assertSee('¿Duplicar este producto?')
        ->assertSee('action="'.route('admin.productos.destroy', $producto).'"', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee('¿Estás seguro de eliminar este producto?')
        ->assertSee('S/ 9.50')
        ->assertSee('SKU: TAM-001');

    $emptyResponse = $this->actingAs($admin)
        ->get(route('admin.productos.index', ['search' => 'does-not-exist']));

    $emptyResponse->assertOk()
        ->assertSee('No hay productos que coincidan')
        ->assertSee('Intenta con otros criterios de búsqueda')
        ->assertSee('href="'.route('admin.productos.index').'"', false)
        ->assertSee('Limpiar filtros')
        ->assertDontSee('role="group" aria-label="Acciones de producto"', false);
});

function assertPendingOrderCardTarget(string $html, string $expectedHref): void
{
    $document = new DOMDocument;
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);
    $links = $xpath->query(sprintf(
        '//div[contains(concat(" ", normalize-space(@class), " "), " card ")][.//*[normalize-space() = "Pedidos Pendientes"]]//a[@href = "%s"]',
        $expectedHref,
    ));

    expect($links)->not->toBeFalse()
        ->and($links->length)->toBe(1);
}

function assertAdminPendingOrderNotificationTarget(string $html, string $expectedHref): void
{
    $document = new DOMDocument;
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);
    $links = $xpath->query(sprintf(
        '//nav[@aria-label = "Barra superior de administración"]//a[@href = "%s" and contains(normalize-space(), "pedidos pendientes")]',
        $expectedHref,
    ));

    expect($links)->not->toBeFalse()
        ->and($links->length)->toBe(1);
}
