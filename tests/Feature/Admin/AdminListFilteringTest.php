<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Testing\TestResponse;

function createAdminListCliente(array $attributes = []): Cliente
{
    return Cliente::create(array_merge([
        'nombre' => 'List client',
        'apellido' => 'Example',
        'email' => null,
        'telefono' => null,
        'estado' => 'activo',
    ], $attributes));
}

function createAdminListPedido(Cliente $cliente, Empleado $empleado): Pedido
{
    return Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => '2026-07-14',
        'hora' => '08:00:00',
        'total' => 10,
        'estado' => 'pendiente',
    ]);
}

function adminListResponse(TestResponse $response, string $variable): LengthAwarePaginator
{
    $response->assertOk()->assertViewHas($variable);

    return $response->viewData($variable);
}

beforeEach(function (): void {
    $this->admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
});

it('combines product filters and searches only the product name', function (): void {
    $matching = Producto::create([
        'nombre' => 'Morning Coffee',
        'descripcion' => 'Filtered product',
        'categoria' => 'bebida',
        'precio' => 8,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    Producto::create([
        'nombre' => 'Coffee Bread',
        'categoria' => 'panaderia',
        'precio' => 6,
        'stock' => 10,
        'estado' => 'activo',
    ]);
    Producto::create([
        'nombre' => 'Iced Coffee',
        'categoria' => 'bebida',
        'precio' => 9,
        'stock' => 10,
        'estado' => 'inactivo',
    ]);
    Producto::create([
        'nombre' => 'Morning Tea',
        'descripcion' => 'Coffee appears outside the name',
        'categoria' => 'bebida',
        'precio' => 7,
        'stock' => 10,
        'estado' => 'activo',
    ]);

    $productos = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.productos.index', [
            'search' => 'Coffee',
            'categoria' => 'bebida',
            'estado' => 'activo',
        ])),
        'productos',
    );

    expect($productos->pluck('id')->all())->toBe([$matching->id]);
});

it('keeps product filters in pagination links', function (): void {
    foreach (range(1, 11) as $index) {
        Producto::create([
            'nombre' => "Coffee {$index}",
            'categoria' => 'bebida',
            'precio' => 5,
            'stock' => 10,
            'estado' => 'activo',
        ]);
    }

    $productos = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.productos.index', [
            'search' => 'Coffee',
            'categoria' => 'bebida',
            'estado' => 'activo',
            'page' => 2,
        ])),
        'productos',
    );
    $paginationQuery = [];
    parse_str((string) parse_url($productos->previousPageUrl(), PHP_URL_QUERY), $paginationQuery);

    expect($productos->perPage())->toBe(10)
        ->and($productos->currentPage())->toBe(2)
        ->and($productos->total())->toBe(11)
        ->and($paginationQuery)->toBe([
            'search' => 'Coffee',
            'categoria' => 'bebida',
            'estado' => 'activo',
            'page' => '1',
        ]);
});

it('filters low-stock products for administrators and preserves the filter in pagination', function (): void {
    $lowStock = Producto::create([
        'nombre' => 'Admin low stock',
        'categoria' => 'bebida',
        'precio' => 5,
        'stock' => 5,
        'stock_minimo' => 5,
        'estado' => 'activo',
    ]);
    $sufficientStock = Producto::create([
        'nombre' => 'Admin sufficient stock',
        'categoria' => 'bebida',
        'precio' => 5,
        'stock' => 6,
        'stock_minimo' => 5,
        'estado' => 'activo',
    ]);
    $alertsDisabled = Producto::create([
        'nombre' => 'Admin alerts disabled',
        'categoria' => 'bebida',
        'precio' => 5,
        'stock' => 0,
        'stock_minimo' => 0,
        'estado' => 'activo',
    ]);

    foreach (range(1, 10) as $index) {
        Producto::create([
            'nombre' => "Admin low stock {$index}",
            'categoria' => 'bebida',
            'precio' => 5,
            'stock' => 1,
            'stock_minimo' => 2,
            'estado' => 'activo',
        ]);
    }

    $productos = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.productos.index', [
            'stock' => 'bajo',
        ])),
        'productos',
    );
    $paginationQuery = [];
    parse_str((string) parse_url($productos->nextPageUrl(), PHP_URL_QUERY), $paginationQuery);
    $secondPage = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.productos.index', [
            'stock' => 'bajo',
            'page' => 2,
        ])),
        'productos',
    );
    $filteredIds = $productos->pluck('id')->merge($secondPage->pluck('id'))->all();

    expect($productos->total())->toBe(11)
        ->and($filteredIds)->toContain($lowStock->id)
        ->and($filteredIds)->not->toContain($sufficientStock->id, $alertsDisabled->id)
        ->and($paginationQuery)->toBe([
            'stock' => 'bajo',
            'page' => '2',
        ]);
});

it('filters low-stock products for workers and ignores unsupported stock values', function (): void {
    $worker = User::factory()->create([
        'rol' => 'trabajador',
    ]);
    $lowStock = Producto::create([
        'nombre' => 'Worker low stock',
        'categoria' => 'bebida',
        'precio' => 5,
        'stock' => 8,
        'stock_minimo' => 8,
        'estado' => 'activo',
    ]);
    $sufficientStock = Producto::create([
        'nombre' => 'Worker sufficient stock',
        'categoria' => 'bebida',
        'precio' => 5,
        'stock' => 9,
        'stock_minimo' => 8,
        'estado' => 'activo',
    ]);

    $filtered = adminListResponse(
        $this->actingAs($worker)->get(route('trabajador.productos.index', [
            'stock' => 'bajo',
        ])),
        'productos',
    );
    $unsupported = adminListResponse(
        $this->actingAs($worker)->get(route('trabajador.productos.index', [
            'stock' => 'low',
        ])),
        'productos',
    );

    expect($filtered->pluck('id')->all())->toBe([$lowStock->id])
        ->and($filtered->pluck('id')->all())->not->toContain($sufficientStock->id)
        ->and($unsupported->pluck('id')->all())->toContain($lowStock->id, $sufficientStock->id);
});

it('searches all client fields with status and includes pedidos count', function (): void {
    $employee = Empleado::create([
        'nombre' => 'List employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
    $byName = createAdminListCliente(['nombre' => 'Needle Name', 'email' => 'name@example.test']);
    $byLastName = createAdminListCliente(['nombre' => 'Last', 'apellido' => 'Needle Last', 'email' => 'last@example.test']);
    $byEmail = createAdminListCliente(['nombre' => 'Email', 'email' => 'needle@example.test']);
    $byPhone = createAdminListCliente(['nombre' => 'Phone', 'email' => 'phone@example.test', 'telefono' => '555-NEEDLE']);
    createAdminListCliente(['nombre' => 'Needle Inactive', 'email' => 'inactive@example.test', 'estado' => 'inactivo']);
    createAdminListCliente(['nombre' => 'Unrelated', 'email' => 'unrelated@example.test']);
    createAdminListPedido($byEmail, $employee);

    $clientes = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.clientes.index', [
            'search' => 'Needle',
            'estado' => 'activo',
        ])),
        'clientes',
    );

    expect($clientes->pluck('id')->all())->toBe([
        $byEmail->id,
        $byLastName->id,
        $byName->id,
        $byPhone->id,
    ])->and($clientes->every(fn (Cliente $cliente): bool => isset($cliente->pedidos_count)))->toBeTrue()
        ->and($clientes->firstWhere('id', $byEmail->id)->pedidos_count)->toBe(1);
});

it('applies every recognized client sort mode', function (?string $sort, array $expectedNames): void {
    $employee = Empleado::create([
        'nombre' => 'Sort employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
    $alpha = createAdminListCliente(['nombre' => 'Alpha', 'apellido' => 'Sort Fixture', 'email' => 'alpha@example.test']);
    $beta = createAdminListCliente(['nombre' => 'Beta', 'apellido' => 'Sort Fixture', 'email' => 'beta@example.test']);
    $gamma = createAdminListCliente(['nombre' => 'Gamma', 'apellido' => 'Sort Fixture', 'email' => 'gamma@example.test']);

    Cliente::query()->whereKey($alpha->id)->update(['created_at' => '2026-01-01 08:00:00']);
    Cliente::query()->whereKey($gamma->id)->update(['created_at' => '2026-02-01 08:00:00']);
    Cliente::query()->whereKey($beta->id)->update(['created_at' => '2026-03-01 08:00:00']);

    createAdminListPedido($alpha, $employee);
    createAdminListPedido($beta, $employee);
    createAdminListPedido($beta, $employee);
    createAdminListPedido($beta, $employee);
    createAdminListPedido($gamma, $employee);
    createAdminListPedido($gamma, $employee);

    $parameters = ['search' => 'Sort Fixture'];

    if ($sort !== null) {
        $parameters['sort'] = $sort;
    }
    $clientes = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.clientes.index', $parameters)),
        'clientes',
    );

    expect($clientes->pluck('nombre')->all())->toBe($expectedNames);
})->with([
    'default name ascending' => [null, ['Alpha', 'Beta', 'Gamma']],
    'name ascending' => ['nombre_asc', ['Alpha', 'Beta', 'Gamma']],
    'name descending' => ['nombre_desc', ['Gamma', 'Beta', 'Alpha']],
    'newest first' => ['reciente', ['Beta', 'Gamma', 'Alpha']],
    'oldest first' => ['antiguo', ['Alpha', 'Gamma', 'Beta']],
    'most pedidos first' => ['pedidos_desc', ['Beta', 'Gamma', 'Alpha']],
]);

it('uses client per page and keeps pagination parameters', function (): void {
    foreach (range(1, 26) as $index) {
        createAdminListCliente([
            'nombre' => sprintf('Page Client %02d', $index),
            'email' => "page{$index}@example.test",
        ]);
    }

    $clientes = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.clientes.index', [
            'search' => 'Page Client',
            'estado' => 'activo',
            'sort' => 'nombre_desc',
            'per_page' => 25,
            'page' => 2,
        ])),
        'clientes',
    );
    $paginationQuery = [];
    parse_str((string) parse_url($clientes->previousPageUrl(), PHP_URL_QUERY), $paginationQuery);

    expect($clientes->perPage())->toBe(25)
        ->and($clientes->currentPage())->toBe(2)
        ->and($clientes->total())->toBe(26)
        ->and($clientes->count())->toBe(1)
        ->and($paginationQuery)->toBe([
            'search' => 'Page Client',
            'estado' => 'activo',
            'sort' => 'nombre_desc',
            'per_page' => '25',
            'page' => '1',
        ]);
});

it('bounds admin client page sizes', function (mixed $requested, int $expected): void {
    $parameters = $requested === null ? [] : ['per_page' => $requested];

    $clientes = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.clientes.index', $parameters)),
        'clientes',
    );

    expect($clientes->perPage())->toBe($expected);
})->with([
    'missing' => [null, 10],
    'ten' => [10, 10],
    'twenty five' => [25, 25],
    'fifty' => [50, 50],
    'one hundred' => [100, 100],
    'nonnumeric' => ['many', 10],
    'zero' => [0, 10],
    'negative' => [-10, 10],
    'unsupported' => [75, 10],
    'oversized' => [1000, 10],
]);

it('bounds admin employee page sizes', function (mixed $requested, int $expected): void {
    $parameters = $requested === null ? [] : ['per_page' => $requested];

    $empleados = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.empleados.index', $parameters)),
        'empleados',
    );

    expect($empleados->perPage())->toBe($expected);
})->with([
    'missing' => [null, 10],
    'ten' => [10, 10],
    'twenty five' => [25, 25],
    'fifty' => [50, 50],
    'one hundred' => [100, 100],
    'nonnumeric' => ['many', 10],
    'zero' => [0, 10],
    'negative' => [-10, 10],
    'unsupported' => [75, 10],
    'oversized' => [1000, 10],
]);

it('allows only safe employee sort directions', function (mixed $direction, array $expectedNames): void {
    Empleado::create([
        'nombre' => 'Alpha Employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
    Empleado::create([
        'nombre' => 'Beta Employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);

    $empleados = adminListResponse(
        $this->actingAs($this->admin)->get(route('admin.empleados.index', [
            'sort' => 'nombre',
            'direction' => $direction,
        ])),
        'empleados',
    );

    expect($empleados->pluck('nombre')->all())->toBe($expectedNames);
})->with([
    'ascending' => ['asc', ['Alpha Employee', 'Beta Employee']],
    'descending' => ['desc', ['Beta Employee', 'Alpha Employee']],
    'invalid' => ['sideways', ['Alpha Employee', 'Beta Employee']],
]);
