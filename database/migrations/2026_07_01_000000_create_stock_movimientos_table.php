<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo');
            $table->unsignedInteger('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index('producto_id', 'stock_movimientos_producto_id_index');
            $table->index('pedido_id', 'stock_movimientos_pedido_id_index');
            $table->index('tipo', 'stock_movimientos_tipo_index');
            $table->index('created_at', 'stock_movimientos_created_at_index');
            $table->index(['producto_id', 'created_at'], 'stock_movimientos_producto_created_at_index');

            $table->foreign('producto_id', 'stock_movimientos_producto_id_foreign')
                ->references('id')
                ->on('productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('pedido_id', 'stock_movimientos_pedido_id_foreign')
                ->references('id')
                ->on('pedidos')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('user_id', 'stock_movimientos_user_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movimientos');
    }
};
