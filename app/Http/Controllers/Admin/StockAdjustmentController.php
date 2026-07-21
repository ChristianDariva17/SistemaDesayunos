<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Stock\RegisterStockAdjustmentAction;
use App\Enums\StockAdjustmentOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockAdjustmentRequest;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

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

        $result = $registerStockAdjustment->handle(
            productoId: (int) $validated['producto_id'],
            operation: StockAdjustmentOperation::Set,
            quantity: (int) $validated['stock_nuevo'],
            user: $request->user(),
            motivo: $validated['motivo'],
        );

        if ($result->movimiento === null) {
            throw ValidationException::withMessages([
                'stock_nuevo' => 'El nuevo stock debe ser diferente al stock actual.',
            ]);
        }

        return redirect()
            ->route('admin.reportes.stock-movimientos', ['producto_id' => $result->producto->getKey()])
            ->with('success', '✅ Ajuste de stock registrado correctamente.');
    }
}
