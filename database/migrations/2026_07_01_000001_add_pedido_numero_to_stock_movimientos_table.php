<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->string('pedido_numero')->nullable()->after('pedido_id');
        });

        DB::table('stock_movimientos')
            ->whereNotNull('pedido_id')
            ->whereNull('pedido_numero')
            ->update([
                'pedido_numero' => DB::raw('(select numero_pedido from pedidos where pedidos.id = stock_movimientos.pedido_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('stock_movimientos', function (Blueprint $table): void {
            $table->dropColumn('pedido_numero');
        });
    }
};
