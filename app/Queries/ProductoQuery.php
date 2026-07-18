<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class ProductoQuery
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $query = Producto::query();

        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->string('stock')->toString() === 'bajo') {
            $query->stockMinimoBajo();
        }

        return $query->latest()->latest('id')->paginate(10)->withQueryString();
    }
}
