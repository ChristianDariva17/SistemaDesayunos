<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;

it('renders searchable select hooks only on order create workflows', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-searchable-selects@example.test',
        'rol' => 'administrador',
    ]);
    $worker = User::factory()->create([
        'email' => 'worker-searchable-selects@example.test',
        'rol' => 'trabajador',
    ]);

    Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.searchable@example.test',
        'estado' => 'activo',
    ]);
    Empleado::create([
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
        'cliente_id' => Cliente::query()->first()->id,
        'empleado_id' => Empleado::query()->first()->id,
        'fecha' => now()->toDateString(),
        'hora' => '08:30',
        'total' => 12.50,
        'estado' => 'pendiente',
    ]);
    $pedido->productos()->attach($producto->id, [
        'cantidad' => 1,
        'precio_unitario' => 12.50,
        'subtotal' => 12.50,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk()
        ->assertSee('id="cliente_id"', false)
        ->assertSee('id="producto_id"', false)
        ->assertSee('data-searchable-select-placeholder="Selecciona un cliente"', false)
        ->assertSee('data-searchable-select-placeholder="Selecciona un producto"', false)
        ->assertSee('-- Selecciona un producto --', false);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.edit', $pedido))
        ->assertOk()
        ->assertSee('id="cliente_id"', false)
        ->assertSee('id="producto_id"', false)
        ->assertSee('data-searchable-select-placeholder="Selecciona un cliente"', false);

    $this->actingAs($worker)
        ->get(route('trabajador.pedidos.create'))
        ->assertOk()
        ->assertSee('id="cliente_id" class="form-select', false)
        ->assertSee('data-enhance="searchable-select"', false)
        ->assertSee('data-searchable-select-placeholder="Seleccione un producto"', false)
        ->assertSee('Seleccione un producto', false);

    expect(Route::has('trabajador.pedidos.edit'))->toBeFalse();
});

it('renders admin pedido client names with automatic date time values', function (): void {
    Carbon::setTestNow('2026-07-08 14:35:00');

    $admin = User::factory()->create([
        'email' => 'admin-pedido-client-select@example.test',
        'rol' => 'administrador',
    ]);
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.pedidos@example.test',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
    $pedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => '2026-07-07',
        'hora' => '09:10:00',
        'total' => 0,
        'estado' => 'pendiente',
    ]);

    try {
        $this->actingAs($admin)
            ->get(route('admin.pedidos.create'))
            ->assertOk()
            ->assertSee('Ana Paredes', false)
            ->assertSee('value="2026-07-08"', false)
            ->assertSee('value="14:35"', false)
            ->assertDontSee('ana.pedidos@example.test', false);

        $this->actingAs($admin)
            ->get(route('admin.pedidos.edit', $pedido))
            ->assertOk()
            ->assertSee('Ana Paredes', false)
            ->assertSee('value="2026-07-07"', false)
            ->assertSee('value="09:10"', false)
            ->assertDontSee('ana.pedidos@example.test', false);
    } finally {
        Carbon::setTestNow();
    }
});

it('renders an inactive assigned cliente on admin pedido edit by name only', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-inactive-pedido-client@example.test',
        'rol' => 'administrador',
    ]);
    $cliente = Cliente::create([
        'nombre' => 'Marta',
        'apellido' => 'Soto',
        'email' => 'marta.inactive@example.test',
        'estado' => 'inactivo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
    $pedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => '2026-07-07',
        'hora' => '09:10:00',
        'total' => 0,
        'estado' => 'pendiente',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.edit', $pedido))
        ->assertOk()
        ->assertSee('Marta Soto', false)
        ->assertSee('value="'.$cliente->id.'"', false)
        ->assertDontSee('marta.inactive@example.test', false);
});

it('preselects active entities from their contextual detail actions', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $cliente = Cliente::create([
        'nombre' => 'Cliente Contextual',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Empleado Contextual',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $clienteCreateUrl = route('admin.pedidos.create', ['cliente_id' => $cliente->id]);
    $empleadoCreateUrl = route('admin.pedidos.create', ['empleado_id' => $empleado->id]);

    $this->actingAs($admin)
        ->get(route('admin.clientes.show', $cliente))
        ->assertOk()
        ->assertSee('href="'.$clienteCreateUrl.'"', false);

    $this->actingAs($admin)
        ->get(route('admin.empleados.show', $empleado))
        ->assertOk()
        ->assertSee('href="'.$empleadoCreateUrl.'"', false);

    $this->actingAs($admin)
        ->get($clienteCreateUrl)
        ->assertOk()
        ->assertSee('value="'.$cliente->id.'" selected', false);

    $this->actingAs($admin)
        ->get($empleadoCreateUrl)
        ->assertOk()
        ->assertSee('value="'.$empleado->id.'" selected', false);
});

it('hides contextual order actions for inactive entities', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $cliente = Cliente::create([
        'nombre' => 'Cliente Inactivo Contextual',
        'estado' => 'inactivo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Empleado Inactivo Contextual',
        'rol_operativo' => 'mesero',
        'estado' => 'inactivo',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.clientes.show', $cliente))
        ->assertOk()
        ->assertDontSee(route('admin.pedidos.create', ['cliente_id' => $cliente->id]), false);

    $this->actingAs($admin)
        ->get(route('admin.empleados.show', $empleado))
        ->assertOk()
        ->assertDontSee(route('admin.pedidos.create', ['empleado_id' => $empleado->id]), false);
});

it('ignores invalid and inactive contextual order ids', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $inactiveCliente = Cliente::create([
        'nombre' => 'Cliente No Elegible',
        'estado' => 'inactivo',
    ]);
    $inactiveEmpleado = Empleado::create([
        'nombre' => 'Empleado No Elegible',
        'rol_operativo' => 'mesero',
        'estado' => 'inactivo',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create', [
            'cliente_id' => $inactiveCliente->id,
            'empleado_id' => $inactiveEmpleado->id,
        ]))
        ->assertOk()
        ->assertViewHas('cliente_seleccionado', null)
        ->assertViewHas('empleado_seleccionado', null)
        ->assertDontSee('value="'.$inactiveCliente->id.'" selected', false)
        ->assertDontSee('value="'.$inactiveEmpleado->id.'" selected', false);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create', ['cliente_id' => 'invalid', 'empleado_id' => PHP_INT_MAX]))
        ->assertOk()
        ->assertViewHas('cliente_seleccionado', null)
        ->assertViewHas('empleado_seleccionado', null);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk()
        ->assertViewHas('cliente_seleccionado', null)
        ->assertViewHas('empleado_seleccionado', null);
});

it('prioritizes old order input over contextual preselection', function (): void {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $contextCliente = Cliente::create([
        'nombre' => 'Cliente del Contexto',
        'estado' => 'activo',
    ]);
    $oldCliente = Cliente::create([
        'nombre' => 'Cliente Anterior',
        'estado' => 'activo',
    ]);
    $contextEmpleado = Empleado::create([
        'nombre' => 'Empleado del Contexto',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
    $oldEmpleado = Empleado::create([
        'nombre' => 'Empleado Anterior',
        'rol_operativo' => 'cajero',
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->withSession(['_old_input' => [
            'cliente_id' => $oldCliente->id,
            'empleado_id' => $oldEmpleado->id,
        ]])
        ->get(route('admin.pedidos.create', [
            'cliente_id' => $contextCliente->id,
            'empleado_id' => $contextEmpleado->id,
        ]))
        ->assertOk()
        ->assertSee('value="'.$oldCliente->id.'" selected', false)
        ->assertSee('value="'.$oldEmpleado->id.'" selected', false);
});

it('renders the deterministic generic cliente on admin pedido create and edit', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-generic-pedido-client@example.test',
        'rol' => 'administrador',
    ]);
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.generic-test@example.test',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
    $pedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => '2026-07-07',
        'hora' => '09:10:00',
        'total' => 0,
        'estado' => 'pendiente',
    ]);

    $this->assertDatabaseHas('clientes', [
        'nombre' => 'Clientes varios',
        'apellido' => null,
        'email' => null,
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk()
        ->assertSee('Clientes varios', false);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.edit', $pedido))
        ->assertOk()
        ->assertSee('Clientes varios', false);
});

it('renders progressive ajax hooks for high ROI admin list filters', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-ajax-list-hooks@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.productos.index'))
        ->assertOk()
        ->assertSee('id="searchForm"', false)
        ->assertSee('data-ajax-filter="true"', false)
        ->assertSee('data-ajax-target="#productosResults"', false)
        ->assertSee('id="productosResults" data-ajax-region', false);

    $this->actingAs($admin)
        ->get(route('admin.clientes.index'))
        ->assertOk()
        ->assertSee('id="filterForm"', false)
        ->assertSee('data-ajax-auto-submit="true"', false)
        ->assertSee('data-ajax-target="#clientesResults"', false)
        ->assertSee('id="clientesResults" data-ajax-region', false);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.index'))
        ->assertOk()
        ->assertSee('id="filtrosForm"', false)
        ->assertSee('data-ajax-auto-submit="true"', false)
        ->assertSee('data-ajax-target="#pedidosResults"', false)
        ->assertSee('id="pedidosResults" data-ajax-region', false);
});

it('renders the admin pedido create side panel as a single sticky stack', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-pedido-side-panel@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk()
        ->assertSee('class="pedido-side-panel"', false)
        ->assertSee('.pedido-side-panel', false)
        ->assertSee('max-height: calc(100vh - var(--header-height, 70px) - 40px);', false)
        ->assertSee('z-index: 1;', false)
        ->assertSee('@media (max-width: 991.98px)', false)
        ->assertDontSee('class="card shadow-sm border-0 mb-4 sticky-top"', false)
        ->assertDontSee('style="top: 20px;"', false);
});

it('renders admin empleado create preview in the scoped sticky stack', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-empleado-create-side-panel@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.empleados.create'))
        ->assertOk()
        ->assertSee('class="empleado-side-panel"', false)
        ->assertSee('class="card shadow-sm border-0 mb-4 empleado-preview-card"', false)
        ->assertSee('Vista Previa')
        ->assertSee('max-height: calc(100vh - var(--header-height, 70px) - 40px);', false)
        ->assertSee('@media (max-width: 991.98px)', false)
        ->assertDontSee('class="card shadow-sm border-0 mb-4 sticky-top"', false)
        ->assertDontSee('style="top: 20px;"', false);
});

it('renders admin pedido edit side cards in the scoped sticky stack', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-pedido-edit-side-panel@example.test',
        'rol' => 'administrador',
    ]);
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
    $pedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => now()->toDateString(),
        'hora' => '08:30',
        'total' => 0,
        'estado' => 'pendiente',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.edit', $pedido))
        ->assertOk()
        ->assertSee('class="pedido-side-panel"', false)
        ->assertSee('class="card shadow-sm border-0 mb-4 pedido-summary-card"', false)
        ->assertSee('Guía de Edición')
        ->assertSee('max-height: calc(100vh - var(--header-height, 70px) - 40px);', false)
        ->assertSee('@media (max-width: 991.98px)', false)
        ->assertDontSee('class="card shadow-sm border-0 mb-4 sticky-top"', false)
        ->assertDontSee('style="top: 20px;"', false);
});

it('renders admin empleado edit preview in the scoped sticky stack', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-empleado-edit-side-panel@example.test',
        'rol' => 'administrador',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.empleados.edit', $empleado))
        ->assertOk()
        ->assertSee('class="empleado-side-panel"', false)
        ->assertSee('class="card shadow-sm border-0 mb-4 empleado-preview-card"', false)
        ->assertSee('Vista Previa')
        ->assertSee('max-height: calc(100vh - var(--header-height, 70px) - 40px);', false)
        ->assertDontSee('class="card shadow-sm border-0 mb-4 sticky-top"', false)
        ->assertDontSee('style="top: 20px;"', false);
});

it('renders admin pedido show side cards in the scoped sticky stack', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-pedido-show-side-panel@example.test',
        'rol' => 'administrador',
    ]);
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
    $pedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => now()->toDateString(),
        'hora' => '08:30',
        'total' => 0,
        'estado' => 'pendiente',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.show', $pedido))
        ->assertOk()
        ->assertSee('class="pedido-side-panel"', false)
        ->assertSee('class="card shadow-sm border-0 mb-4 pedido-summary-card"', false)
        ->assertSee('Resumen del Pedido')
        ->assertSee('max-height: calc(100vh - var(--header-height, 70px) - 40px);', false)
        ->assertSee('@media (max-width: 991.98px)', false)
        ->assertDontSee('class="card shadow-sm border-0 mb-4 sticky-top"', false)
        ->assertDontSee('style="top: 20px;"', false);
});

it('renders admin pedidos filters directly without a collapse dropdown', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-pedidos-visible-filters@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.index'))
        ->assertOk()
        ->assertSee('pedidos-filters-card', false)
        ->assertSee('class="pedidos-filters-content"', false)
        ->assertSee('id="filtrosForm"', false)
        ->assertSee('id="search"', false)
        ->assertSee('id="estado" name="estado"', false)
        ->assertSee('id="fecha_desde"', false)
        ->assertSee('.pedidos-filters-card .pedidos-filters-content', false)
        ->assertSee('overflow: visible;', false)
        ->assertDontSee('Mostrar/Ocultar')
        ->assertDontSee('data-bs-toggle="collapse"', false)
        ->assertDontSee('id="filtrosCollapse"', false);
});

it('reuses a pre-existing generic cliente with nullable differences in the deterministic setup', function (): void {
    Cliente::where('nombre', 'Clientes varios')->delete();

    $existingGenericCliente = Cliente::create([
        'nombre' => 'Clientes varios',
        'apellido' => 'Temporal',
        'email' => 'clientes.varios.existing@example.test',
        'estado' => 'inactivo',
    ]);

    $migration = require database_path('migrations/2026_07_08_000001_ensure_generic_cliente_exists.php');
    $migration->up();

    expect(Cliente::where('nombre', 'Clientes varios')->count())->toBe(1);

    $this->assertDatabaseHas('clientes', [
        'id' => $existingGenericCliente->id,
        'nombre' => 'Clientes varios',
        'apellido' => null,
        'email' => null,
        'estado' => 'activo',
    ]);
});

it('does not create duplicate generic clientes when rendering admin pedido forms', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-generic-no-duplicates@example.test',
        'rol' => 'administrador',
    ]);
    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.no-duplicates@example.test',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);
    $pedido = Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => '2026-07-07',
        'hora' => '09:10:00',
        'total' => 0,
        'estado' => 'pendiente',
    ]);
    $genericClientesBefore = Cliente::where('nombre', 'Clientes varios')->count();

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.pedidos.edit', $pedido))
        ->assertOk();

    expect(Cliente::where('nombre', 'Clientes varios')->count())->toBe($genericClientesBefore);
});

it('keeps searchable select initialization idempotent for dynamic rows', function (): void {
    $script = searchableSelectInitializationScript();

    $process = new Process(['node', '--input-type=module']);
    $process->setInput($script);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput().$process->getOutput());
});

it('imports Tom Select through the Vite application asset', function (): void {
    $script = file_get_contents(resource_path('js/app.js'));

    expect($script)->toContain("import TomSelect from 'tom-select/base';")
        ->and($script)->toContain("import 'tom-select/dist/css/tom-select.bootstrap5.css';")
        ->and($script)->toContain('window.enhanceSearchableSelect = enhanceSearchableSelect;')
        ->and($script)->toContain('window.clearSearchableSelect = clearSearchableSelect;');
});

function searchableSelectInitializationScript(): string
{
    $appJs = file_get_contents(resource_path('js/app.js'));
    $helpers = searchableSelectExportedFunctionSource($appJs, 'enhanceSearchableSelect')
        .PHP_EOL
        .searchableSelectExportedFunctionSource($appJs, 'enhanceSearchableSelects')
        .PHP_EOL
        .searchableSelectExportedFunctionSource($appJs, 'clearSearchableSelect');

    return <<<JS
{$helpers}

const assert = (condition, message) => {
    if (!condition) {
        throw new Error(message);
    }
};

let createdInstances = 0;
class FakeTomSelect {
    constructor(element, config) {
        createdInstances++;
        this.element = element;
        this.config = config;
        this.cleared = false;
        this.blurred = false;
        element.tomselect = this;
    }

    clear(silent) {
        this.cleared = silent;
    }

    blur() {
        this.blurred = true;
    }
}

const emptyOption = { value: '', textContent: 'Seleccione un producto' };
const firstSelect = {
    dataset: {
        searchableSelectPlaceholder: 'Buscar producto',
    },
    querySelector(selector) {
        return selector === 'option[value=""]' ? emptyOption : null;
    },
};
const secondSelect = {
    dataset: {},
    querySelector(selector) {
        return selector === 'option[value=""]' ? emptyOption : null;
    },
};
const root = {
    querySelectorAll(selector) {
        return selector === 'select[data-enhance="searchable-select"]' ? [firstSelect, secondSelect] : [];
    },
};

const instances = enhanceSearchableSelects(root, FakeTomSelect);
assert(createdInstances === 2, 'expected two Tom Select instances on first pass');
assert(instances[0] === firstSelect.tomselect, 'first instance was not returned');
assert(firstSelect.tomselect.config.allowEmptyOption === true, 'empty option should be allowed');
assert(firstSelect.tomselect.config.create === false, 'ad-hoc option creation should stay disabled');
assert(firstSelect.tomselect.config.placeholder === 'Buscar producto', 'dataset placeholder was not used');
assert(secondSelect.tomselect.config.placeholder === 'Seleccione un producto', 'empty option placeholder was not used');

enhanceSearchableSelects(root, FakeTomSelect);
assert(createdInstances === 2, 'selects were initialized twice');

clearSearchableSelect(firstSelect);
assert(firstSelect.tomselect.cleared === true, 'Tom Select instance was not cleared silently');
assert(firstSelect.tomselect.blurred === true, 'Tom Select instance was not blurred after clearing');
JS;
}

function searchableSelectExportedFunctionSource(string $source, string $functionName): string
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
