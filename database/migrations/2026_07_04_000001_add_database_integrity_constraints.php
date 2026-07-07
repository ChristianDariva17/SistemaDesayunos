<?php

declare(strict_types=1);

use App\Support\Database\CheckConstraints;
use App\Support\Database\MigrationPreflight;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array{table:string, expression:string}>
     */
    private const CHECKS = [
        'productos_stock_non_negative_check' => ['table' => 'productos', 'expression' => 'stock >= 0'],
        'productos_precio_non_negative_check' => ['table' => 'productos', 'expression' => 'precio >= 0'],
        'pedidos_total_non_negative_check' => ['table' => 'pedidos', 'expression' => 'total >= 0'],
        'pedidos_impuesto_non_negative_check' => ['table' => 'pedidos', 'expression' => 'impuesto >= 0'],
        'pedidos_estado_check' => ['table' => 'pedidos', 'expression' => "estado in ('pendiente', 'procesando', 'completado', 'cancelado')"],
        'pedido_producto_cantidad_positive_check' => ['table' => 'pedido_producto', 'expression' => 'cantidad > 0'],
        'pedido_producto_precio_unitario_non_negative_check' => ['table' => 'pedido_producto', 'expression' => 'precio_unitario >= 0'],
        'pedido_producto_subtotal_non_negative_check' => ['table' => 'pedido_producto', 'expression' => 'subtotal >= 0'],
        'stock_movimientos_tipo_check' => ['table' => 'stock_movimientos', 'expression' => "tipo in ('entrada', 'salida', 'ajuste', 'devolucion', 'cancelacion')"],
        'stock_movimientos_cantidad_positive_check' => ['table' => 'stock_movimientos', 'expression' => 'cantidad > 0'],
        'stock_movimientos_stock_anterior_non_negative_check' => ['table' => 'stock_movimientos', 'expression' => 'stock_anterior >= 0'],
        'stock_movimientos_stock_nuevo_non_negative_check' => ['table' => 'stock_movimientos', 'expression' => 'stock_nuevo >= 0'],
    ];

    public function up(): void
    {
        $this->assertRequiredColumnsExist();
        $this->assertExistingDataIsValid();

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->index('fecha', 'pedidos_fecha_index');
        });

        if (! CheckConstraints::supports()) {
            return;
        }

        foreach (self::CHECKS as $name => $definition) {
            CheckConstraints::add($definition['table'], $name, $definition['expression']);
        }
    }

    public function down(): void
    {
        if (CheckConstraints::supports()) {
            foreach (array_reverse(self::CHECKS) as $name => $definition) {
                CheckConstraints::drop($definition['table'], $name);
            }
        }

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropIndex('pedidos_fecha_index');
        });
    }

    private function assertExistingDataIsValid(): void
    {
        $invalidChecks = [
            'productos.stock' => DB::table('productos')->where('stock', '<', 0)->exists(),
            'productos.precio' => DB::table('productos')->where('precio', '<', 0)->exists(),
            'pedidos.total' => DB::table('pedidos')->where('total', '<', 0)->exists(),
            'pedidos.impuesto' => DB::table('pedidos')->where('impuesto', '<', 0)->exists(),
            'pedidos.estado' => DB::table('pedidos')->whereNotIn('estado', ['pendiente', 'procesando', 'completado', 'cancelado'])->exists(),
            'pedido_producto.cantidad' => DB::table('pedido_producto')->where('cantidad', '<=', 0)->exists(),
            'pedido_producto.precio_unitario' => DB::table('pedido_producto')->where('precio_unitario', '<', 0)->exists(),
            'pedido_producto.subtotal' => DB::table('pedido_producto')->where('subtotal', '<', 0)->exists(),
            'stock_movimientos.tipo' => DB::table('stock_movimientos')->whereNotIn('tipo', ['entrada', 'salida', 'ajuste', 'devolucion', 'cancelacion'])->exists(),
            'stock_movimientos.cantidad' => DB::table('stock_movimientos')->where('cantidad', '<=', 0)->exists(),
            'stock_movimientos.stock_anterior' => DB::table('stock_movimientos')->where('stock_anterior', '<', 0)->exists(),
            'stock_movimientos.stock_nuevo' => DB::table('stock_movimientos')->where('stock_nuevo', '<', 0)->exists(),
        ];

        $failedChecks = array_keys(array_filter($invalidChecks));

        if ($failedChecks !== []) {
            throw new RuntimeException(
                'Cannot add integrity constraints while invalid data exists in: '.implode(', ', $failedChecks).'. '.
                'Fix or remove the invalid rows listed above, then rerun php artisan migrate.'
            );
        }
    }

    private function assertRequiredColumnsExist(): void
    {
        MigrationPreflight::assertColumnsExist([
            'productos' => ['stock', 'precio'],
            'pedidos' => ['fecha', 'total', 'impuesto', 'estado'],
            'pedido_producto' => ['cantidad', 'precio_unitario', 'subtotal'],
            'stock_movimientos' => ['tipo', 'cantidad', 'stock_anterior', 'stock_nuevo'],
        ], basename(__FILE__));
    }
};
