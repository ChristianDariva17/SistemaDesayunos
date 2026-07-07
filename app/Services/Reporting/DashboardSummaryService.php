<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class DashboardSummaryService
{
    private const CACHE_KEY = 'reporting.dashboard-summary.v1';

    public function summary(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addSeconds(60),
            fn (): array => $this->buildSummary(),
        );
    }

    private function buildSummary(): array
    {
        $productStats = $this->productStats();
        $clientStats = $this->clientStats();
        $orderStats = $this->orderStats();

        return [
            'totalProductos' => (int) ($productStats->total_productos ?? 0),
            'productosActivos' => (int) ($productStats->productos_activos ?? 0),
            'stockBajo' => (int) Producto::stockBajo()->count(),
            'totalClientes' => (int) ($clientStats->total_clientes ?? 0),
            'clientesActivos' => (int) ($clientStats->clientes_activos ?? 0),
            'totalPedidos' => (int) ($orderStats->total_pedidos ?? 0),
            'pedidosPendientes' => (int) ($orderStats->pedidos_pendientes ?? 0),
            'pedidosCompletados' => (int) ($orderStats->pedidos_completados ?? 0),
            'totalVentas' => (string) ($orderStats->total_ventas ?? '0'),
            'ventasMes' => (string) ($orderStats->ventas_mes ?? '0'),
            'totalEmpleados' => Empleado::query()->count(),
            'productosMasVendidos' => $this->topSellingProducts(),
        ];
    }

    private function productStats(): object
    {
        return Producto::query()
            ->selectRaw('COUNT(*) as total_productos')
            ->selectRaw("SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as productos_activos")
            ->first() ?? (object) [];
    }

    private function clientStats(): object
    {
        return Cliente::query()
            ->selectRaw('COUNT(*) as total_clientes')
            ->selectRaw("SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as clientes_activos")
            ->first() ?? (object) [];
    }

    private function orderStats(): object
    {
        return Pedido::query()
            ->selectRaw('COUNT(*) as total_pedidos')
            ->selectRaw("SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pedidos_pendientes")
            ->selectRaw("SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as pedidos_completados")
            ->selectRaw("COALESCE(SUM(CASE WHEN estado = 'completado' THEN total ELSE 0 END), 0) as total_ventas")
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN estado = 'completado' AND created_at BETWEEN ? AND ? THEN total ELSE 0 END), 0) as ventas_mes",
                [now()->startOfMonth()->toDateTimeString(), now()->endOfMonth()->toDateTimeString()],
            )
            ->first() ?? (object) [];
    }

    private function topSellingProducts()
    {
        return DB::table('pedido_producto')
            ->join('productos', 'pedido_producto.producto_id', '=', 'productos.id')
            ->join('pedidos', 'pedido_producto.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.estado', 'completado')
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.categoria',
                DB::raw('SUM(pedido_producto.cantidad) as total_vendido'),
                DB::raw('SUM(pedido_producto.subtotal) as ingresos'),
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.categoria')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();
    }
}
