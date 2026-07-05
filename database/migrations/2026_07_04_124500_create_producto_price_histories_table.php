<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_price_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('precio', 10, 2);
            $table->timestamp('effective_from');
            $table->timestamp('effective_to')->nullable();
            $table->timestamps();

            $table->index(['producto_id', 'effective_from'], 'producto_price_histories_producto_effective_from_index');
            $table->index(['producto_id', 'effective_to'], 'producto_price_histories_producto_effective_to_index');
        });

        $now = now();

        DB::table('productos')
            ->select(['id', 'precio', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(100, function ($productos) use ($now): void {
                foreach ($productos as $producto) {
                    DB::table('producto_price_histories')->insert([
                        'producto_id' => $producto->id,
                        'precio' => $producto->precio,
                        'effective_from' => $producto->created_at ?? $now,
                        'effective_to' => null,
                        'created_at' => $producto->created_at ?? $now,
                        'updated_at' => $producto->updated_at ?? $now,
                    ]);
                }
            }, 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_price_histories');
    }
};
