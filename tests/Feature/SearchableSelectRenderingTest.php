<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
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
