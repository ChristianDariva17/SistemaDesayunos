<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clientes')) {
            return;
        }

        $genericCliente = DB::table('clientes')
            ->where('nombre', 'Clientes varios')
            ->orderByRaw("CASE WHEN estado = 'activo' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();

        $now = now();

        if ($genericCliente) {
            DB::table('clientes')
                ->where('id', $genericCliente->id)
                ->update([
                    'apellido' => null,
                    'email' => null,
                    'estado' => 'activo',
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('clientes')->insert([
            'nombre' => 'Clientes varios',
            'apellido' => null,
            'telefono' => null,
            'email' => null,
            'direccion' => null,
            'fecha_nacimiento' => null,
            'estado' => 'activo',
            'notas' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('clientes') || ! Schema::hasTable('pedidos')) {
            return;
        }

        $genericCliente = DB::table('clientes')
            ->where('nombre', 'Clientes varios')
            ->whereNull('apellido')
            ->whereNull('email')
            ->orderBy('id')
            ->first();

        if (! $genericCliente) {
            return;
        }

        $isUsedByPedidos = DB::table('pedidos')
            ->where('cliente_id', $genericCliente->id)
            ->exists();

        if (! $isUsedByPedidos) {
            DB::table('clientes')->where('id', $genericCliente->id)->delete();
        }
    }
};
