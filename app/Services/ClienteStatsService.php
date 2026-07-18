<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cliente;

final class ClienteStatsService
{
    /**
     * @return array{
     *     totalClientes: int,
     *     clientesActivos: int,
     *     clientesInactivos: int,
     *     nuevosEsteMes: int
     * }
     */
    public function indexSummary(): array
    {
        $monthStart = now()->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();

        $summary = Cliente::query()
            ->selectRaw(
                'COUNT(*) AS total_clientes,
                SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) AS clientes_activos,
                SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) AS clientes_inactivos,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) AS nuevos_este_mes',
                ['activo', 'inactivo', $monthStart, $nextMonthStart],
            )
            ->firstOrFail();

        return [
            'totalClientes' => (int) $summary->getAttribute('total_clientes'),
            'clientesActivos' => (int) $summary->getAttribute('clientes_activos'),
            'clientesInactivos' => (int) $summary->getAttribute('clientes_inactivos'),
            'nuevosEsteMes' => (int) $summary->getAttribute('nuevos_este_mes'),
        ];
    }
}
