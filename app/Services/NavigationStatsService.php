<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pedido;
use App\Models\Producto;

final class NavigationStatsService
{
    /**
     * @return array{stockBajo: int, pedidosPendientes: int}
     */
    public function getLayoutStats(): array
    {
        return [
            'stockBajo' => Producto::query()
                ->where('stock', '<', 10)
                ->count(),
            'pedidosPendientes' => Pedido::query()
                ->where('estado', 'pendiente')
                ->count(),
        ];
    }
}
