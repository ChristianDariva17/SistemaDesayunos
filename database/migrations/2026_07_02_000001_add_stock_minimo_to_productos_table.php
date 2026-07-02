<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->unsignedInteger('stock_minimo')
                ->default(0)
                ->after('stock');

            $table->index(['stock_minimo', 'stock'], 'productos_stock_minimo_stock_index');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropIndex('productos_stock_minimo_stock_index');
            $table->dropColumn('stock_minimo');
        });
    }
};
