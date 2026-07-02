<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Stock\RegisterStockAdjustmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockAdjustmentRequest;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class StockAdjustmentController extends Controller
{
    public function create(): View
    {
        $productos = Producto::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'stock']);

        return view('admin.stock-adjustments.create', compact('productos'));
    }

    public function store(
        StoreStockAdjustmentRequest $request,
        RegisterStockAdjustmentAction $registerStockAdjustment,
    ): RedirectResponse {
        $validated = $request->validated();

        $movement = $registerStockAdjustment->handle(
            productoId: (int) $validated['producto_id'],
            stockNuevo: (int) $validated['stock_nuevo'],
            user: $request->user(),
            motivo: $validated['motivo'],
        );

        return redirect()
            ->route('admin.reportes.stock-movimientos', ['producto_id' => $movement->producto_id])
            ->with('success', '✅ Ajuste de stock registrado correctamente.');
    }
}
