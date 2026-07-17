<?php

declare(strict_types=1);

namespace App\Actions\Producto;

use App\Enums\ProductoEstado;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

final class ToggleProductoStatusAction
{
    public function handle(Producto $producto): Producto
    {
        $estadoAnterior = $producto->estado;
        $estado = ProductoEstado::tryFrom((string) $producto->estado) ?? ProductoEstado::Inactive;
        $nuevoEstado = $estado->toggled()->value;

        $producto->update(['estado' => $nuevoEstado]);

        Log::info('Estado de producto cambiado', [
            'producto_id' => $producto->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado,
        ]);

        return $producto;
    }
}
