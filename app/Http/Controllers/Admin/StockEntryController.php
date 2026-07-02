<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Stock\RegisterStockEntryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockEntryRequest;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class StockEntryController extends Controller
{
    public function create(): View
    {
        $productos = Producto::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'stock']);

        return view('admin.stock-entries.create', compact('productos'));
    }

    public function store(StoreStockEntryRequest $request, RegisterStockEntryAction $registerStockEntry): RedirectResponse
    {
        $validated = $request->validated();

        $movement = $registerStockEntry->handle(
            productoId: (int) $validated['producto_id'],
            cantidad: (int) $validated['cantidad'],
            user: $request->user(),
            motivo: $validated['motivo'] ?? null,
        );

        return redirect()
            ->route('admin.reportes.stock-movimientos', ['producto_id' => $movement->producto_id])
            ->with('success', '✅ Entrada de stock registrada correctamente.');
    }
}
