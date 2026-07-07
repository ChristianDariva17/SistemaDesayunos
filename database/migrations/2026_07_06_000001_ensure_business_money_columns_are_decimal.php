<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $moneyColumns = [
        'productos' => ['precio'],
        'producto_price_histories' => ['precio'],
        'pedidos' => ['total', 'impuesto'],
        'pedido_producto' => ['precio_unitario', 'subtotal'],
        'daily_cash_closures' => ['total_revenue'],
    ];

    /**
     * @var array<string, string>
     */
    private array $defaultZeroColumns = [
        'pedidos.impuesto' => '0',
        'daily_cash_closures.total_revenue' => '0',
    ];

    public function up(): void
    {
        foreach ($this->moneyColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $this->alterMoneyColumn($table, $column);
            }
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: the previous float/double type cannot be inferred safely.
    }

    private function alterMoneyColumn(string $table, string $column): void
    {
        $defaultSql = array_key_exists("{$table}.{$column}", $this->defaultZeroColumns) ? ' DEFAULT 0' : '';

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement("ALTER TABLE {$table} MODIFY {$column} DECIMAL(10, 2) NOT NULL{$defaultSql}"),
            'pgsql' => $this->alterPostgresMoneyColumn($table, $column),
            'sqlsrv' => DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DECIMAL(10, 2) NOT NULL"),
            default => null,
        };
    }

    private function alterPostgresMoneyColumn(string $table, string $column): void
    {
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE NUMERIC(10, 2) USING ROUND({$column}::numeric, 2)");

        if (array_key_exists("{$table}.{$column}", $this->defaultZeroColumns)) {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} SET DEFAULT 0");
        }
    }
};
