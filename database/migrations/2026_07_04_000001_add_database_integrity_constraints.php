<?php

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
        $this->assertExistingDataIsValid();

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->index('fecha', 'pedidos_fecha_index');
        });

        if (! $this->supportsCheckConstraints()) {
            return;
        }

        foreach (self::CHECKS as $name => $definition) {
            DB::statement(sprintf(
                'ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s)',
                $definition['table'],
                $name,
                $definition['expression'],
            ));
        }
    }

    public function down(): void
    {
        if ($this->supportsCheckConstraints()) {
            foreach (array_reverse(self::CHECKS) as $name => $definition) {
                DB::statement($this->dropConstraintSql($definition['table'], $name));
            }
        }

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropIndex('pedidos_fecha_index');
        });
    }

    private function supportsCheckConstraints(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'pgsql', 'sqlsrv'], true);
    }

    private function dropConstraintSql(string $table, string $name): string
    {
        return match (DB::getDriverName()) {
            'mysql' => "ALTER TABLE {$table} DROP CHECK {$name}",
            'pgsql' => "ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}",
            'sqlsrv' => "ALTER TABLE {$table} DROP CONSTRAINT {$name}",
            default => throw new RuntimeException('Unsupported database driver for check constraints.'),
        };
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
            throw new RuntimeException('Cannot add integrity constraints while invalid data exists in: '.implode(', ', $failedChecks));
        }
    }
};
