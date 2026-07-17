<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Pedido;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class PedidoQuery
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $query = Pedido::query()
            ->with(['empleado:id,nombre,rol_operativo', 'cliente:id,nombre,apellido,email'])
            ->withCount('productos');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function (Builder $query) use ($search): void {
                $query->where('numero_pedido', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function (Builder $query) use ($search): void {
                        $query->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->get('estado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->get('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->get('fecha_hasta'));
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->get('fecha'));
        }

        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->get('empleado_id'));
        }

        return $query->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(15)
            ->withQueryString();
    }
}
