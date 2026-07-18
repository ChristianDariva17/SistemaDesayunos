<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;

/**
 * @return array{producto: Producto, expected_order_ids: list<int>, expected_client_names: list<string>, oldest_client_name: string}
 */
function productDetailRecentOrdersFixture(): array
{
    $producto = Producto::create([
        'nombre' => 'Recent Orders Product',
        'categoria' => 'bebida',
        'precio' => 12.50,
        'stock' => 20,
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Recent Orders Employee',
        'rol_operativo' => 'ventas',
        'estado' => 'activo',
    ]);
    $pedidos = collect();

    foreach (range(1, 6) as $position) {
        $cliente = Cliente::create([
            'nombre' => "Recent Client {$position}",
            'estado' => 'activo',
        ]);
        $pedido = Pedido::create([
            'cliente_id' => $cliente->id,
            'empleado_id' => $empleado->id,
            'metodo_pago' => 'efectivo',
            'fecha' => now()->toDateString(),
            'hora' => '09:30:00',
            'impuesto' => 0,
            'total' => 12.50,
            'estado' => 'completado',
        ]);
        $pedido->forceFill([
            'created_at' => now()->subMinutes(6 - $position),
            'updated_at' => now()->subMinutes(6 - $position),
        ])->save();
        $pedido->productos()->attach($producto->id, [
            'cantidad' => $position,
            'precio_unitario' => '12.50',
            'subtotal' => number_format(12.50 * $position, 2, '.', ''),
        ]);
        $pedidos->push($pedido);
    }

    $recentPedidos = $pedidos->sortByDesc('created_at')->take(5);

    return [
        'producto' => $producto,
        'expected_order_ids' => $recentPedidos->pluck('id')->all(),
        'expected_client_names' => collect(range(2, 6))
            ->reverse()
            ->map(fn (int $position): string => "Recent Client {$position}")
            ->values()
            ->all(),
        'oldest_client_name' => 'Recent Client 1',
    ];
}

function getProductDetailWithoutLazyLoading(User $user, string $routeName, Producto $producto): TestResponse
{
    $wasPreventingLazyLoading = Model::preventsLazyLoading();
    Model::preventLazyLoading(true);

    try {
        return test()->actingAs($user)->get(route($routeName, $producto));
    } finally {
        Model::preventLazyLoading($wasPreventingLazyLoading);
    }
}

it('renders the admin product recent orders with their clients eagerly loaded', function (): void {
    $fixture = productDetailRecentOrdersFixture();
    $response = getProductDetailWithoutLazyLoading(
        User::factory()->create(['rol' => 'administrador']),
        'admin.productos.show',
        $fixture['producto'],
    );

    $response->assertOk()
        ->assertViewHas('producto', function (Producto $producto) use ($fixture): bool {
            return $producto->pedidos->pluck('id')->all() === $fixture['expected_order_ids']
                && $producto->pedidos->every(fn (Pedido $pedido): bool => $pedido->relationLoaded('cliente'))
                && $producto->pedidos->every(fn (Pedido $pedido): bool => $pedido->pivot !== null
                    && $pedido->pivot->cantidad !== null
                    && $pedido->pivot->precio_unitario !== null
                    && $pedido->pivot->subtotal !== null);
        })
        ->assertSeeTextInOrder($fixture['expected_client_names'])
        ->assertDontSeeText($fixture['oldest_client_name']);
});

it('renders the worker product recent orders with their clients eagerly loaded', function (): void {
    $fixture = productDetailRecentOrdersFixture();
    $response = getProductDetailWithoutLazyLoading(
        User::factory()->create(['rol' => 'trabajador']),
        'trabajador.productos.show',
        $fixture['producto'],
    );

    $response->assertOk()
        ->assertViewHas('producto', function (Producto $producto) use ($fixture): bool {
            return $producto->pedidos->pluck('id')->all() === $fixture['expected_order_ids']
                && $producto->pedidos->every(fn (Pedido $pedido): bool => $pedido->relationLoaded('cliente'))
                && $producto->pedidos->every(fn (Pedido $pedido): bool => $pedido->pivot !== null
                    && $pedido->pivot->cantidad !== null
                    && $pedido->pivot->precio_unitario !== null
                    && $pedido->pivot->subtotal !== null);
        })
        ->assertSeeTextInOrder($fixture['expected_client_names'])
        ->assertDontSeeText($fixture['oldest_client_name']);
});
