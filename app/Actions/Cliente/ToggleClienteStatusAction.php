<?php

declare(strict_types=1);

namespace App\Actions\Cliente;

use App\Models\Cliente;
use Illuminate\Support\Facades\Log;

final class ToggleClienteStatusAction
{
    public function handle(Cliente $cliente): Cliente
    {
        $estadoAnterior = $cliente->estado;
        $nuevoEstado = $cliente->estado === 'activo' ? 'inactivo' : 'activo';

        $cliente->update(['estado' => $nuevoEstado]);

        Log::info('Estado de cliente cambiado', [
            'cliente_id' => $cliente->id,
            'nombre_completo' => trim($cliente->nombre.' '.($cliente->apellido ?? '')),
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado,
            'usuario' => auth()->id() ?? 'Sistema',
        ]);

        return $cliente;
    }
}
