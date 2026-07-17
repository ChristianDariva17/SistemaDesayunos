<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PedidoStatus;
use App\Models\Pedido;

final class PedidoStatsService
{
    /**
     * @return array{total_pedidos: int, pendientes: int, completados: int, ventas_hoy: mixed, ventas_mes: mixed}
     */
    public function get(): array
    {
        return [
            'total_pedidos' => Pedido::count(),
            'pendientes' => Pedido::where('estado', PedidoStatus::Pending->value)->count(),
            'completados' => Pedido::where('estado', PedidoStatus::Completed->value)->count(),
            'ventas_hoy' => Pedido::whereDate('fecha', today())
                ->where('estado', PedidoStatus::Completed->value)
                ->sum('total'),
            'ventas_mes' => Pedido::whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->where('estado', PedidoStatus::Completed->value)
                ->sum('total'),
        ];
    }
}
