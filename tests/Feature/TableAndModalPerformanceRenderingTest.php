<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Symfony\Component\Process\Process;

it('renders one reusable admin product image and stock modal instead of per-row modals', function (): void {
    Storage::fake('public');
    $admin = User::factory()->create([
        'email' => 'admin-table-modal-performance@example.test',
        'rol' => 'administrador',
    ]);

    Producto::create([
        'nombre' => 'Pan con pollo',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'comidas',
        'precio' => 8.50,
        'stock' => 12,
        'stock_minimo' => 3,
        'estado' => 'activo',
        'imagen' => 'productos/pan.jpg',
    ]);

    Producto::create([
        'nombre' => 'Café pasado',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebidas',
        'precio' => 4.00,
        'stock' => 20,
        'stock_minimo' => 5,
        'estado' => 'activo',
        'imagen' => 'productos/cafe.jpg',
    ]);
    Storage::disk('public')->put('productos/pan.jpg', 'image');
    Storage::disk('public')->put('productos/cafe.jpg', 'image');

    $response = $this->actingAs($admin)
        ->get(route('admin.productos.index'))
        ->assertOk()
        ->assertSee('data-bs-target="#productImageModal"', false)
        ->assertSee('data-bs-target="#productStockModal"', false)
        ->assertSee('id="productImageModal"', false)
        ->assertSee('id="productStockModal"', false)
        ->assertSee('id="productStockModalForm" action="#" method="POST" data-reset-on-show="true"', false)
        ->assertDontSee('id="imageModal', false)
        ->assertDontSee('id="stockModal', false)
        ->assertSee('responsive-card-table', false)
        ->assertSee('loading="lazy"', false);

    expect(substr_count($response->getContent(), 'id="productImageModal"'))->toBe(1)
        ->and(substr_count($response->getContent(), 'id="productStockModal"'))->toBe(1);
});

it('protects the reusable product stock modal from stale form state', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-stock-modal-behavior@example.test',
        'rol' => 'administrador',
    ]);

    $firstProduct = Producto::create([
        'nombre' => 'Pan integral',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'comidas',
        'precio' => 6.50,
        'stock' => 7,
        'stock_minimo' => 2,
        'estado' => 'activo',
    ]);

    $secondProduct = Producto::create([
        'nombre' => 'Jugo natural',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'bebidas',
        'precio' => 5.00,
        'stock' => 18,
        'stock_minimo' => 4,
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.productos.index'))
        ->assertOk()
        ->assertSee('id="productStockModal"', false)
        ->assertSee('data-product-stock-name="Pan integral"', false)
        ->assertSee('data-product-stock-name="Jugo natural"', false);

    $script = tableModalPerformanceProductStockModalScript([
        'first' => [
            'action' => route('admin.productos.actualizar-stock', $firstProduct),
            'name' => $firstProduct->nombre,
            'current' => (string) $firstProduct->stock,
        ],
        'second' => [
            'action' => route('admin.productos.actualizar-stock', $secondProduct),
            'name' => $secondProduct->nombre,
            'current' => (string) $secondProduct->stock,
        ],
    ]);

    $process = new Process(['node', '--input-type=module']);
    $process->setInput($script);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput().$process->getOutput());
});

it('renders order indexes with eager product counts and responsive table metadata', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-order-count-performance@example.test',
        'rol' => 'administrador',
    ]);

    $worker = User::factory()->create([
        'email' => 'worker-order-count-performance@example.test',
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.performance@example.test',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich mixto',
        'categoria' => 'comidas',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $pedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => now()->toDateString(),
        'hora' => '08:30',
        'total' => 25.00,
        'estado' => 'pendiente',
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 12.50,
        'subtotal' => 25.00,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.index'))
        ->assertOk()
        ->assertSee('responsive-card-table', false)
        ->assertSee('data-label="Total"', false)
        ->assertSee('1 productos');

    $this->actingAs($worker)
        ->get(route('trabajador.pedidos.index'))
        ->assertOk()
        ->assertSee('responsive-card-table', false)
        ->assertSee('data-label="Total"', false)
        ->assertSee('1 productos');
});

it('renders client and employee indexes as responsive tables with complete cell labels', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-responsive-lists@example.test',
        'rol' => 'administrador',
    ]);
    $worker = User::factory()->create([
        'email' => 'worker-responsive-lists@example.test',
        'rol' => 'trabajador',
    ]);

    Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.responsive@example.test',
        'telefono' => '555-0101',
        'ciudad' => 'Lima',
        'estado' => 'activo',
    ]);
    Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    assertResponsiveTableLabels(
        $this->actingAs($admin)->get(route('admin.clientes.index'))->assertOk(),
        ['ID', 'Cliente', 'Contacto', 'Pedidos', 'Estado', 'Acciones'],
    );
    assertResponsiveTableLabels(
        $this->actingAs($worker)->get(route('trabajador.clientes.index'))->assertOk(),
        ['ID', 'Cliente', 'Teléfono', 'Ciudad', 'Estado'],
    );
    assertResponsiveTableLabels(
        $this->actingAs($admin)->get(route('admin.empleados.index'))->assertOk(),
        ['#', 'Empleado', 'Rol', 'Estado', 'Fecha Registro', 'Acciones'],
    );
});

it('renders admin inventory reports as responsive tables with complete cell labels', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-responsive-reports@example.test',
        'rol' => 'administrador',
    ]);
    $producto = Producto::create([
        'nombre' => 'Café de reporte',
        'categoria' => 'bebidas',
        'stock' => 14,
        'stock_minimo' => 5,
        'estado' => 'activo',
        'precio' => 4.50,
    ]);

    StockMovimiento::create([
        'producto_id' => $producto->id,
        'pedido_id' => null,
        'pedido_numero' => 'PED-RESPONSIVE-001',
        'user_id' => $admin->id,
        'tipo' => StockMovimiento::TIPO_ENTRADA,
        'cantidad' => 4,
        'stock_anterior' => 10,
        'stock_nuevo' => 14,
        'motivo' => 'Responsive report fixture',
    ]);

    assertResponsiveTableLabels(
        $this->actingAs($admin)->get(route('admin.reportes.stock-movimientos'))->assertOk(),
        ['Producto', 'Pedido', 'Usuario / Actor', 'Tipo', 'Cantidad', 'Stock anterior', 'Stock nuevo', 'Motivo', 'Fecha'],
    );
    assertResponsiveTableLabels(
        $this->actingAs($admin)->get(route('admin.reportes.resumen-inventario'))->assertOk(),
        ['Producto', 'Estado', 'Stock actual', 'Stock mínimo', 'Entradas', 'Salidas', 'Ajustes', 'Último movimiento', 'Situación'],
    );
});

it('renders responsive report empty states full width without a mobile label region', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-responsive-report-empty-states@example.test',
        'rol' => 'administrador',
    ]);

    assertResponsiveTableEmptyState(
        $this->actingAs($admin)->get(route('admin.reportes.stock-movimientos'))->assertOk(),
        'No hay movimientos para mostrar',
    );
    assertResponsiveTableEmptyState(
        $this->actingAs($admin)->get(route('admin.reportes.resumen-inventario'))->assertOk(),
        'No hay productos para mostrar',
    );

    $css = file_get_contents(resource_path('css/app.css'));
    $mobileBreakpoint = '@media (max-width: 767.98px)';

    expect($css)->toContain($mobileBreakpoint);

    $mobileCss = substr($css, strpos($css, $mobileBreakpoint));

    expect($mobileCss)->toMatch('/\.responsive-card-table tbody td\[colspan\]\s*\{[^}]*display:\s*block;[^}]*width:\s*100%;[^}]*text-align:\s*center\s*!important;[^}]*\}/s')
        ->toMatch('/\.responsive-card-table tbody td\[colspan\]::before\s*\{[^}]*content:\s*none;[^}]*\}/s');
});

function assertResponsiveTableLabels(TestResponse $response, array $expectedLabels): void
{
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $table = $xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " responsive-card-table ")]')->item(0);

    expect($table)->not->toBeNull();

    $cells = $xpath->query('.//tbody/tr[td[@data-label]][1]/td', $table);
    $labels = [];

    foreach ($cells as $cell) {
        $labels[] = $cell->getAttribute('data-label');
    }

    expect($labels)->toBe($expectedLabels);
}

function assertResponsiveTableEmptyState(TestResponse $response, string $expectedText): void
{
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $cell = $xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " responsive-card-table ")]//tbody/tr/td[@colspan = "9"]')->item(0);

    expect($cell)->not->toBeNull()
        ->and($cell->hasAttribute('data-label'))->toBeFalse()
        ->and($cell->textContent)->toContain($expectedText);
}

function tableModalPerformanceProductStockModalScript(array $products): string
{
    $appJs = file_get_contents(resource_path('js/app.js'));
    $handlers = tableModalPerformanceExportedFunctionSource($appJs, 'resetProductStockModalForm')
        .PHP_EOL
        .tableModalPerformanceExportedFunctionSource($appJs, 'registerProductStockModalHandlers');
    $fixtureJson = json_encode($products, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return <<<JS
{$handlers}

const products = {$fixtureJson};
const assert = (condition, message) => {
    if (!condition) {
        throw new Error(message);
    }
};

class FakeModal {
    constructor() {
        this.listeners = {};
    }

    addEventListener(name, callback) {
        this.listeners[name] = callback;
    }

    trigger(name, relatedTarget = undefined) {
        this.listeners[name]?.({ relatedTarget });
    }
}

const fields = {
    tipo: { value: 'incrementar', defaultValue: 'incrementar' },
    cantidad: { value: '', defaultValue: '' },
    motivo: { value: '', defaultValue: '' },
};

const form = {
    action: '#',
    reset() {
        Object.values(fields).forEach((field) => {
            field.value = field.defaultValue;
        });
    },
};

const title = { textContent: 'Actualizar Stock' };
const current = { textContent: '0' };
const documentRef = {
    getElementById(id) {
        return {
            productStockModalForm: form,
            productStockModalLabel: title,
            productStockModalCurrent: current,
        }[id] ?? null;
    },
};

const modal = new FakeModal();
registerProductStockModalHandlers(modal, documentRef);

fields.tipo.value = 'decrementar';
fields.cantidad.value = '99';
fields.motivo.value = 'stale first reason';
modal.trigger('show.bs.modal', {
    dataset: {
        productStockAction: products.first.action,
        productStockName: products.first.name,
        productStockCurrent: products.first.current,
    },
});

assert(form.action === products.first.action, 'first action was not applied');
assert(title.textContent === 'Actualizar Stock - ' + products.first.name, 'first title was not applied');
assert(current.textContent === products.first.current, 'first current stock was not applied');
assert(fields.tipo.value === 'incrementar', 'tipo was not reset on first show');
assert(fields.cantidad.value === '', 'cantidad was not reset on first show');
assert(fields.motivo.value === '', 'motivo was not reset on first show');

fields.tipo.value = 'establecer';
fields.cantidad.value = '42';
fields.motivo.value = 'stale second reason';
modal.trigger('show.bs.modal', {
    dataset: {
        productStockAction: products.second.action,
        productStockName: products.second.name,
        productStockCurrent: products.second.current,
    },
});

assert(form.action === products.second.action, 'second action was not applied');
assert(title.textContent === 'Actualizar Stock - ' + products.second.name, 'second title was not applied');
assert(current.textContent === products.second.current, 'second current stock was not applied');
assert(fields.tipo.value === 'incrementar', 'tipo was not reset on second show');
assert(fields.cantidad.value === '', 'cantidad was not reset on second show');
assert(fields.motivo.value === '', 'motivo was not reset on second show');

fields.tipo.value = 'decrementar';
fields.cantidad.value = '12';
fields.motivo.value = 'hide stale reason';
modal.trigger('hidden.bs.modal');

assert(form.action === '#', 'action was not reset on hidden');
assert(title.textContent === 'Actualizar Stock', 'title was not reset on hidden');
assert(current.textContent === '0', 'current stock was not reset on hidden');
assert(fields.tipo.value === 'incrementar', 'tipo was not reset on hidden');
assert(fields.cantidad.value === '', 'cantidad was not reset on hidden');
assert(fields.motivo.value === '', 'motivo was not reset on hidden');
JS;
}

function tableModalPerformanceExportedFunctionSource(string $source, string $functionName): string
{
    $start = strpos($source, 'export function '.$functionName);

    if ($start === false) {
        throw new RuntimeException("Could not find exported function {$functionName} in resources/js/app.js.");
    }

    $braceStart = strpos($source, '{', $start);
    $depth = 0;
    $length = strlen($source);

    for ($index = $braceStart; $index < $length; $index++) {
        if ($source[$index] === '{') {
            $depth++;
        }

        if ($source[$index] === '}') {
            $depth--;

            if ($depth === 0) {
                return str_replace('export function', 'function', substr($source, $start, $index - $start + 1));
            }
        }
    }

    throw new RuntimeException("Could not extract exported function {$functionName} from resources/js/app.js.");
}
