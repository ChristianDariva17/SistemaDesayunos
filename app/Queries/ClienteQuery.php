<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ClienteQuery
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $query = Cliente::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $query) use ($search): void {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        switch ($request->get('sort', 'nombre_asc')) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'reciente':
                $query->orderBy('created_at', 'desc');
                break;
            case 'antiguo':
                $query->orderBy('created_at', 'asc');
                break;
            case 'pedidos_desc':
                $query->withCount('pedidos')->orderBy('pedidos_count', 'desc');
                break;
        }

        $requestedPerPage = filter_var($request->input('per_page', 10), FILTER_VALIDATE_INT);
        $perPage = in_array($requestedPerPage, [10, 25, 50, 100], true) ? $requestedPerPage : 10;

        return $query->withCount('pedidos')->paginate($perPage)->withQueryString();
    }
}
