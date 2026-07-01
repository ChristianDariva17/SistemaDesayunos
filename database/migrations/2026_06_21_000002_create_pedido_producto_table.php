<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_producto', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_id');
            $table->foreignId('producto_id');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->index('producto_id', 'pedido_producto_producto_id_index');
            $table->unique(['pedido_id', 'producto_id'], 'pedido_producto_pedido_producto_unique');

            $table->foreign('pedido_id', 'pedido_producto_pedido_id_foreign')
                ->references('id')
                ->on('pedidos')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('producto_id', 'pedido_producto_producto_id_foreign')
                ->references('id')
                ->on('productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_producto');
    }
};
