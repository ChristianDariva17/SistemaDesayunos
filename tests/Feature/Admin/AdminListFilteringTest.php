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
