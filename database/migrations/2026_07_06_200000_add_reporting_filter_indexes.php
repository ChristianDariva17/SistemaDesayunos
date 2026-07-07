<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->index(['fecha', 'estado'], 'pedidos_fecha_estado_index');
            $table->index(['metodo_pago', 'fecha'], 'pedidos_metodo_pago_fecha_index');
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->index('categoria', 'productos_categoria_index');
        });

        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->index(['tipo', 'created_at'], 'stock_movimientos_tipo_created_at_index');
            $table->index(['user_id', 'created_at'], 'stock_movimientos_user_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->dropIndex('stock_movimientos_user_created_at_index');
            $table->dropIndex('stock_movimientos_tipo_created_at_index');
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->dropIndex('productos_categoria_index');
        });

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropIndex('pedidos_metodo_pago_fecha_index');
            $table->dropIndex('pedidos_fecha_estado_index');
        });
    }
};
