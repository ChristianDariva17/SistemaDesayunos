<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        if (! $this->supportsCheckConstraints()) {
            return;
        }

        DB::statement("ALTER TABLE stock_reservations ADD CONSTRAINT stock_reservations_status_check CHECK (status in ('active', 'released', 'consumed', 'cancelled'))");
        DB::statement('ALTER TABLE stock_reservations ADD CONSTRAINT stock_reservations_cantidad_positive_check CHECK (cantidad > 0)');
    }

    public function down(): void
    {
        if ($this->supportsCheckConstraints()) {
            DB::statement($this->dropConstraintSql('stock_reservations', 'stock_reservations_cantidad_positive_check'));
            DB::statement($this->dropConstraintSql('stock_reservations', 'stock_reservations_status_check'));
        }

        Schema::dropIfExists('stock_reservations');
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
};
