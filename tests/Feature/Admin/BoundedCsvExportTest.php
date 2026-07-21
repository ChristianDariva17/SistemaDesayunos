<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** @return list<list<string|null>> */
function boundedCsvRows(string $csv): array
{
    $stream = fopen('php://temp', 'r+');
    fwrite($stream, substr($csv, 3));
    rewind($stream);

    $rows = [];

    while (($row = fgetcsv($stream)) !== false) {
        $rows[] = $row;
    }

    fclose($stream);

    return $rows;
}

/** @return list<string> */
function boundedSelectQueries(string $table): array
{
    return collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(function (string $query) use ($table): bool {
            preg_match('/\bfrom "([^"]+)"/', $query, $matches);

            return ($matches[1] ?? null) === $table;
        })
        ->values()
        ->all();
}

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-20 10:11:12');
    config()->set('reportes.csv_chunk_size', 2);

    $this->admin = User::factory()->create([
        'rol' => 'administrador',
    ]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('streams the exact filtered product CSV in bounded deterministic chunks', function (): void {
    $productos = collect(range(1, 5))->map(fn (int $index): Producto => Producto::create([
        'nombre' => 'Bounded product',
        'descripcion' => "Description {$index}",
        'categoria' => 'bebida',
        'precio' => 4 + $index,
        'stock' => $index,
        'codigo_barras' => "BAR-{$index}",
        'sku' => "SKU-{$index}",
        'estado' => 'activo',
    ]));

    Producto::create([
        'nombre' => 'Bounded product excluded',
        'categoria' => 'bebida',
        'precio' => 99,
        'stock' => 1,
        'estado' => 'inactivo',
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->actingAs($this->admin)->get(route('admin.productos.exportar', [
        'search' => 'Bounded product',
        'categoria' => 'bebida',
        'estado' => 'activo',
    ]));
    $csv = $response->streamedContent();

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="productos_2026-07-20_101112.csv"');

    $expectedRows = $productos->map(fn (Producto $producto): array => [
        (string) $producto->id,
        $producto->nombre,
        $producto->descripcion,
        $producto->categoria,
        $producto->precio,
        (string) $producto->stock,
        $producto->codigo_barras,
        $producto->sku,
        'Activo',
        '20/07/2026 10:11:12',
    ])->all();
    $queries = boundedSelectQueries('productos');

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->and(boundedCsvRows($csv))->toBe(array_merge([[
            'ID', 'Nombre', 'Descripción', 'Categoría', 'Precio', 'Stock', 'Código de Barras', 'SKU', 'Estado', 'Fecha de Creación',
        ]], $expectedRows))
        ->and($queries)->toHaveCount(3)
        ->and($queries)->each->toContain('limit 2');
});

it('streams the exact filtered client CSV in bounded deterministic chunks', function (): void {
    $clientes = collect(range(1, 5))->map(fn (int $index): Cliente => Cliente::create([
        'nombre' => 'Bounded client',
        'apellido' => "Surname {$index}",
        'telefono' => "555-000{$index}",
        'email' => "bounded{$index}@example.com",
        'direccion' => "Address {$index}",
        'fecha_nacimiento' => '2000-07-20',
        'estado' => 'activo',
        'notas' => "Note {$index}",
    ]));

    Cliente::create([
        'nombre' => 'Bounded client excluded',
        'apellido' => 'Inactive',
        'estado' => 'inactivo',
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->actingAs($this->admin)->get(route('admin.clientes.exportar', [
        'search' => 'Bounded client',
        'estado' => 'activo',
    ]));
    $csv = $response->streamedContent();

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="clientes_2026-07-20_101112.csv"');

    $expectedRows = $clientes->map(fn (Cliente $cliente): array => [
        (string) $cliente->id,
        $cliente->nombre,
        $cliente->apellido,
        $cliente->telefono,
        $cliente->email,
        $cliente->direccion,
        '20/07/2000',
        now()->diffInYears($cliente->fecha_nacimiento).' años',
        'Activo',
        $cliente->notas,
        '20/07/2026 10:11:12',
    ])->all();
    $queries = boundedSelectQueries('clientes');

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->and(boundedCsvRows($csv))->toBe(array_merge([[
            'ID', 'Nombre', 'Apellido', 'Teléfono', 'Email', 'Dirección', 'Fecha Nacimiento', 'Edad', 'Estado', 'Notas', 'Fecha Registro',
        ]], $expectedRows))
        ->and($queries)->toHaveCount(3)
        ->and($queries)->each->toContain('limit 2');
});

it('streams filtered pedidos with stable ties and eager loading per bounded chunk', function (): void {
    $cliente = Cliente::create([
        'nombre' => 'Bounded',
        'apellido' => 'Customer',
        'estado' => 'activo',
    ]);
    $empleado = Empleado::create([
        'nombre' => 'Bounded Worker',
        'rol_operativo' => 'barista',
        'estado' => 'activo',
    ]);
    $pedidos = collect(range(1, 5))->map(fn (int $index): Pedido => Pedido::create([
        'numero_pedido' => "PED-BOUNDED-{$index}",
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-19',
        'hora' => '08:30:00',
        'total' => 10 + $index,
        'estado' => 'completado',
    ]));

    Pedido::create([
        'numero_pedido' => 'PED-BOUNDED-EXCLUDED',
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'metodo_pago' => 'efectivo',
        'fecha' => '2026-07-19',
        'hora' => '08:30:00',
        'total' => 99,
        'estado' => 'pendiente',
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->actingAs($this->admin)->get(route('admin.pedidos.exportar', [
        'search' => 'PED-BOUNDED',
        'estado' => 'completado',
        'fecha_desde' => '2026-07-19',
        'fecha_hasta' => '2026-07-19',
        'fecha' => '2026-07-19',
        'empleado_id' => $empleado->id,
    ]));
    $csv = $response->streamedContent();

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="pedidos_2026-07-20_101112.csv"');

    $expectedRows = $pedidos->reverse()->values()->map(fn (Pedido $pedido): array => [
        $pedido->numero_pedido,
        'Bounded',
        'Bounded Worker',
        'barista',
        '19/07/2026 08:30',
        number_format((float) $pedido->total, 2),
        'Completado',
    ])->all();
    $pedidoQueries = boundedSelectQueries('pedidos');

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->and(boundedCsvRows($csv))->toBe(array_merge([[
            'Número', 'Cliente', 'Empleado', 'Rol', 'Fecha', 'Total', 'Estado',
        ]], $expectedRows))
        ->and($pedidoQueries)->toHaveCount(3)
        ->and($pedidoQueries)->each->toContain('limit 2')
        ->and(boundedSelectQueries('clientes'))->toHaveCount(3)
        ->and(boundedSelectQueries('empleados'))->toHaveCount(3);
});
