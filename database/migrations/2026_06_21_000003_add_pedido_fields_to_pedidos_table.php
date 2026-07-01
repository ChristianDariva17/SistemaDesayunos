<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedidos') || ! Schema::hasTable('clientes') || ! Schema::hasTable('empleados')) {
            return;
        }

        try {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->dropForeign('pedidos_cliente_id_foreign');
            });
        } catch (Throwable $e) {
            // Ignore legacy databases where the foreign key does not exist yet.
        }

        try {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->dropForeign('pedidos_empleado_id_foreign');
            });
        } catch (Throwable $e) {
            // Ignore legacy databases where the foreign key does not exist yet.
        }

        DB::table('pedidos')
            ->whereNotNull('cliente_id')
            ->whereNotIn('cliente_id', DB::table('clientes')->select('id'))
            ->update(['cliente_id' => null]);

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->string('numero_pedido', 32)->nullable()->after('id');
            $table->unsignedBigInteger('empleado_id')->nullable()->after('cliente_id');
            $table->string('metodo_pago', 32)->nullable()->after('empleado_id');
            $table->date('fecha')->nullable()->after('metodo_pago');
            $table->time('hora')->nullable()->after('fecha');
            $table->decimal('impuesto', 10, 2)->default(0)->after('hora');
            $table->text('observaciones')->nullable()->after('estado');
        });

        $fallbackEmpleadoId = DB::table('empleados')->orderBy('id')->value('id');

        if ($fallbackEmpleadoId === null && DB::table('pedidos')->exists()) {
            $fallbackEmpleadoId = DB::table('empleados')->insertGetId([
                'user_id' => null,
                'nombre' => 'Empleado migrado',
                'rol_operativo' => 'otros',
                'estado' => 'activo',
                'telefono' => null,
                'observaciones' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($fallbackEmpleadoId !== null) {
            DB::table('pedidos')
                ->whereNull('empleado_id')
                ->orWhereNotIn('empleado_id', DB::table('empleados')->select('id'))
                ->update(['empleado_id' => $fallbackEmpleadoId]);
        }

        DB::table('pedidos')
            ->select('id')
            ->where(function ($query): void {
                $query->whereNull('numero_pedido')
                    ->orWhere('numero_pedido', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($pedidos): void {
                foreach ($pedidos as $pedido) {
                    do {
                        $numeroPedido = sprintf('PED-%s-%s', now()->format('Ym'), Str::upper(Str::random(6)));
                    } while (DB::table('pedidos')->where('numero_pedido', $numeroPedido)->exists());

                    DB::table('pedidos')
                        ->where('id', $pedido->id)
                        ->update(['numero_pedido' => $numeroPedido]);
                }
            }, 'id');

        DB::table('pedidos')
            ->orderBy('id')
            ->chunkById(100, function ($pedidos): void {
                foreach ($pedidos as $pedido) {
                    $createdAt = $pedido->created_at ? \Illuminate\Support\Carbon::parse($pedido->created_at) : now();

                    DB::table('pedidos')
                        ->where('id', $pedido->id)
                        ->update([
                            'fecha' => $pedido->fecha ?? $createdAt->toDateString(),
                            'hora' => $pedido->hora ?? $createdAt->format('H:i:s'),
                        ]);
                }
            }, 'id');

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->unsignedBigInteger('empleado_id')->nullable(false)->change();
            $table->string('numero_pedido', 32)->nullable(false)->change();
            $table->date('fecha')->nullable(false)->change();
            $table->time('hora')->nullable(false)->change();
        });

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->index('cliente_id', 'pedidos_cliente_id_index');
            $table->index(['estado', 'created_at'], 'pedidos_estado_created_at_index');
            $table->index(['empleado_id', 'fecha'], 'pedidos_empleado_fecha_index');
            $table->foreign('cliente_id', 'pedidos_cliente_id_foreign')
                ->references('id')
                ->on('clientes')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('empleado_id', 'pedidos_empleado_id_foreign')
                ->references('id')
                ->on('empleados')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->unique('numero_pedido', 'pedidos_numero_pedido_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropUnique('pedidos_numero_pedido_unique');
            $table->dropForeign('pedidos_empleado_id_foreign');
            $table->dropForeign('pedidos_cliente_id_foreign');
            $table->dropIndex('pedidos_empleado_fecha_index');
            $table->dropIndex('pedidos_estado_created_at_index');
            $table->dropIndex('pedidos_cliente_id_index');
            $table->dropColumn([
                'numero_pedido',
                'empleado_id',
                'metodo_pago',
                'fecha',
                'hora',
                'impuesto',
                'observaciones',
            ]);
        });
    }
};
