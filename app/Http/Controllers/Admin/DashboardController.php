<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ==========================================
        // ESTADÍSTICAS GENERALES
        // ==========================================
        
        // Productos
        $totalProductos = Producto::count();
        $productosActivos = Producto::where('estado', 'activo')->count();
        $stockBajo = Producto::stockBajo()->count();
        
        // Clientes
        $totalClientes = Cliente::count();
        $clientesActivos = Cliente::where('estado', 'activo')->count();
        
        // Pedidos
        $totalPedidos = Pedido::count();
        $pedidosPendientes = Pedido::where('estado', 'pendiente')->count();
        $pedidosCompletados = Pedido::where('estado', 'completado')->count();
        
        // Ventas
        $totalVentas = Pedido::where('estado', 'completado')
            ->sum('total') ?? 0;
        
        $ventasMes = Pedido::where('estado', 'completado')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total') ?? 0;
        
        // Empleados
        $totalEmpleados = Empleado::count() ?? 0;
        
        // ==========================================
        // PRODUCTOS MÁS VENDIDOS
        // ==========================================
        $productosMasVendidos = DB::table('detalle_pedidos')
            ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.estado', 'completado')
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.categoria',
                DB::raw('SUM(detalle_pedidos.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_pedidos.subtotal) as ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.categoria')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get();
        
        // ==========================================
        // ÚLTIMOS PEDIDOS
        // ==========================================
        $ultimosPedidos = Pedido::with('cliente')
            ->latest()
            ->take(5)
            ->get();
        
        // ==========================================
        // RETORNAR VISTA CON DATOS
        // ==========================================
        return view('admin.dashboard', compact(
            'totalProductos',
            'productosActivos',
            'stockBajo',
            'totalClientes',
            'clientesActivos',
            'totalPedidos',
            'pedidosPendientes',
            'pedidosCompletados',
            'totalVentas',
            'ventasMes',
            'totalEmpleados',
            'productosMasVendidos',
            'ultimosPedidos'
        ));
    }
}
