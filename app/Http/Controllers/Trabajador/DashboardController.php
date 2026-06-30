<?php

namespace App\Http\Controllers\Trabajador;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard del trabajador
     * 
     * Los trabajadores tienen acceso limitado:
     * - Ver estadísticas de solo lectura
     * - No pueden modificar productos ni clientes
     * - Pueden crear y ver pedidos
     */
    public function index()
    {
        try {
            // ==========================================
            // ESTADÍSTICAS BÁSICAS (SOLO LECTURA)
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
            
            // Ventas totales
            $totalVentas = Pedido::where('estado', 'completado')
                ->sum('total') ?? 0;
            
            // Ventas del mes actual
            $ventasMes = Pedido::where('estado', 'completado')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total') ?? 0;
            
            // ==========================================
            // PEDIDOS DEL TRABAJADOR ACTUAL
            // ==========================================
            // Nota: Si tienes un campo 'created_by' en pedidos, 
            // puedes filtrar solo los pedidos creados por este trabajador
            $misPedidos = Pedido::latest()
                ->take(10)
                ->get();
            
            // ==========================================
            // PRODUCTOS MÁS VENDIDOS (TOP 5)
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
            // ÚLTIMOS PEDIDOS (PARA REFERENCIA)
            // ==========================================
            $ultimosPedidos = Pedido::with('cliente')
                ->latest()
                ->take(5)
                ->get();
            
            // ==========================================
            // INFORMACIÓN DEL TRABAJADOR ACTUAL
            // ==========================================
            $trabajador = Auth::user();
            
            // ==========================================
            // RETORNAR VISTA CON DATOS
            // ==========================================
            return view('trabajador.dashboard', compact(
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
                'misPedidos',
                'productosMasVendidos',
                'ultimosPedidos',
                'trabajador'
            ));
            
        } catch (\Exception $e) {
            Log::error('==========================================');
            Log::error('ERROR EN DASHBOARD TRABAJADOR');
            Log::error('==========================================');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Línea: ' . $e->getLine());
            Log::error('Archivo: ' . $e->getFile());
            
            // Retornar con valores por defecto en caso de error
            return view('trabajador.dashboard', [
                'totalProductos' => 0,
                'productosActivos' => 0,
                'stockBajo' => 0,
                'totalClientes' => 0,
                'clientesActivos' => 0,
                'totalPedidos' => 0,
                'pedidosPendientes' => 0,
                'pedidosCompletados' => 0,
                'totalVentas' => 0,
                'ventasMes' => 0,
                'misPedidos' => collect([]),
                'productosMasVendidos' => collect([]),
                'ultimosPedidos' => collect([]),
                'trabajador' => Auth::user()
            ])->with('error', 'Error al cargar el dashboard. Contacte con el administrador.');
        }
    }
}
