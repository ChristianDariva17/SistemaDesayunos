<?php

declare(strict_types=1);

use App\Support\Database\CheckConstraints;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('cantidad');
            $table->string('status', 32)->default('active');
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();

            $table->index(['producto_id', 'status'], 'stock_reservations_producto_status_index');
            $table->index(['pedido_id', 'status'], 'stock_reservations_pedido_status_index');
            $table->index('status_changed_at', 'stock_reservations_status_changed_at_index');
        });

        if (! CheckConstraints::supports()) {
            return;
        }

        CheckConstraints::add('stock_reservations', 'stock_reservations_status_check', "status in ('active', 'released', 'consumed', 'cancelled')");
        CheckConstraints::add('stock_reservations', 'stock_reservations_cantidad_positive_check', 'cantidad > 0');
    }

    public function down(): void
    {
        if (CheckConstraints::supports()) {
            CheckConstraints::drop('stock_reservations', 'stock_reservations_cantidad_positive_check');
            CheckConstraints::drop('stock_reservations', 'stock_reservations_status_check');
        }

        Schema::dropIfExists('stock_reservations');
    }
};
