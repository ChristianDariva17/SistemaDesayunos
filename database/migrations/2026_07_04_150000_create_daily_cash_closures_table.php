<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_cash_closures', function (Blueprint $table): void {
            $table->id();
            $table->date('business_date')->unique();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->unsignedInteger('settled_order_count')->default(0);
            $table->unsignedInteger('pending_order_count')->default(0);
            $table->unsignedInteger('cancelled_order_count')->default(0);
            $table->json('payment_method_totals')->nullable();
            $table->foreignId('closed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->index(['business_date', 'closed_at'], 'daily_cash_closures_date_closed_at_index');
            $table->index('closed_by_user_id', 'daily_cash_closures_closed_by_user_id_index');
        });

        if (! $this->supportsCheckConstraints()) {
            return;
        }

        DB::statement('ALTER TABLE daily_cash_closures ADD CONSTRAINT daily_cash_closures_total_orders_non_negative_check CHECK (total_orders >= 0)');
        DB::statement('ALTER TABLE daily_cash_closures ADD CONSTRAINT daily_cash_closures_total_revenue_non_negative_check CHECK (total_revenue >= 0)');
        DB::statement('ALTER TABLE daily_cash_closures ADD CONSTRAINT daily_cash_closures_settled_order_count_non_negative_check CHECK (settled_order_count >= 0)');
        DB::statement('ALTER TABLE daily_cash_closures ADD CONSTRAINT daily_cash_closures_pending_order_count_non_negative_check CHECK (pending_order_count >= 0)');
        DB::statement('ALTER TABLE daily_cash_closures ADD CONSTRAINT daily_cash_closures_cancelled_order_count_non_negative_check CHECK (cancelled_order_count >= 0)');
    }

    public function down(): void
    {
        if ($this->supportsCheckConstraints()) {
            DB::statement($this->dropConstraintSql('daily_cash_closures', 'daily_cash_closures_cancelled_order_count_non_negative_check'));
            DB::statement($this->dropConstraintSql('daily_cash_closures', 'daily_cash_closures_pending_order_count_non_negative_check'));
            DB::statement($this->dropConstraintSql('daily_cash_closures', 'daily_cash_closures_settled_order_count_non_negative_check'));
            DB::statement($this->dropConstraintSql('daily_cash_closures', 'daily_cash_closures_total_revenue_non_negative_check'));
            DB::statement($this->dropConstraintSql('daily_cash_closures', 'daily_cash_closures_total_orders_non_negative_check'));
        }

        Schema::dropIfExists('daily_cash_closures');
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
