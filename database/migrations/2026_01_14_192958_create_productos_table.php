<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('categoria');
            $table->decimal('precio', 10, 2);
            $table->string('codigo_barras')->nullable();
            $table->string('sku')->nullable();
            $table->integer('stock')->default(0);
            $table->string('estado')->default('activo');
            $table->timestamps();

            $table->unique('codigo_barras', 'productos_codigo_barras_unique');
            $table->unique('sku', 'productos_sku_unique');
            $table->index(['estado', 'categoria'], 'productos_estado_categoria_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
