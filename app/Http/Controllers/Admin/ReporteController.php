<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Producto;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Empleado;

class ReporteController extends Controller
{
    /**
     * Muestra la vista principal de reportes
     */
    public function index()
    {
        try {
            // Estadísticas de productos
            $totalProductos = Producto::count();
            $productosActivos = Producto::where('estado', 'activo')->count();
            $stockTotal = Producto::sum('stock');
            $stockBajo = Producto::stockBajo()->count();
            $valorInventario = DB::table('productos')
                ->selectRaw('SUM(precio * stock) as total')
                ->value('total') ?? 0;

            // Estadísticas de clientes
            $totalClientes = Cliente::count();
            $clientesActivos = Cliente::where('estado', 'activo')->count();

            // Estadísticas de pedidos
            $totalPedidos = Pedido::count();
            $pedidosCompletados = Pedido::where('estado', 'completado')->count();
            $pedidosPendientes = Pedido::where('estado', 'pendiente')->count();
            $pedidosProcesando = Pedido::where('estado', 'procesando')->count();
            $totalVentas = Pedido::where('estado', 'completado')->sum('total') ?? 0;
            $ventasMesActual = Pedido::where('estado', 'completado')
                ->whereYear('fecha', now()->year)
                ->whereMonth('fecha', now()->month)
                ->sum('total') ?? 0;

            // Estadísticas de empleados
            $totalEmpleados = Empleado::count() ?? 0;

            // Preparar array
            $estadisticas = [
                'totalProductos' => $totalProductos,
                'productosActivos' => $productosActivos,
                'stockTotal' => $stockTotal,
                'stockBajo' => $stockBajo,
                'valorInventario' => $valorInventario,
                'totalClientes' => $totalClientes,
                'clientesActivos' => $clientesActivos,
                'totalPedidos' => $totalPedidos,
                'pedidosCompletados' => $pedidosCompletados,
                'pedidosPendientes' => $pedidosPendientes,
                'pedidosProcesando' => $pedidosProcesando,
                'totalVentas' => $totalVentas,
                'ventasMesActual' => $ventasMesActual,
                'totalEmpleados' => $totalEmpleados,
            ];

            return view('admin.reportes.index', compact('estadisticas'));
        } catch (\Exception $e) {
            Log::error('Error al cargar estadísticas: ' . $e->getMessage());

            $estadisticas = [
                'totalProductos' => 0,
                'productosActivos' => 0,
                'stockTotal' => 0,
                'stockBajo' => 0,
                'valorInventario' => 0,
                'totalClientes' => 0,
                'clientesActivos' => 0,
                'totalPedidos' => 0,
                'pedidosCompletados' => 0,
                'pedidosPendientes' => 0,
                'pedidosProcesando' => 0,
                'totalVentas' => 0,
                'ventasMesActual' => 0,
                'totalEmpleados' => 0,
            ];

            return view('admin.reportes.index', compact('estadisticas'))
                ->with('error', 'Error al cargar estadísticas.');
        }
    }

    /**
     * Genera el reporte de inventario completo en PDF
     */
    public function inventario(Request $request)
    {
        try {
            // 1. OBTENER ACCIÓN (ver o descargar)
            $accion = $request->input('accion', 'descargar');

            // 2. OBTENER TODOS LOS PRODUCTOS
            $productos = Producto::all();

            // 3. CALCULAR TOTALES
            $totalProductos = $productos->count();
            $stockTotal = $productos->sum('stock');

            // 4. CALCULAR VALOR DEL INVENTARIO (precio * stock)
            $valorInventario = DB::table('productos')
                ->selectRaw('SUM(precio * stock) as total')
                ->value('total') ?? 0;

            // 5. GENERAR PDF
            $pdf = PDF::loadView('admin.reportes.inventario', compact(
                'productos',
                'totalProductos',
                'stockTotal',
                'valorInventario'
            ));

            // 6. CONFIGURAR PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable_html5_parser', true);
            $pdf->setOption('isRemoteEnabled', true);

            // 7. NOMBRE DEL ARCHIVO
            $nombreArchivo = 'reporte-inventario-' . now()->format('Y-m-d-His') . '.pdf';

            // 8. RETORNAR SEGÚN ACCIÓN
            if ($accion === 'ver') {
                return $pdf->stream($nombreArchivo);
            } else {
                return $pdf->download($nombreArchivo);
            }
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en reporte de inventario: ' . $e->getMessage());
            Log::error('Línea: ' . $e->getLine());

            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }

    /**
     * Genera el reporte de productos con stock bajo en PDF
     */
    public function stockBajo(Request $request)
    {
        try {
            // 1. OBTENER ACCIÓN
            $accion = $request->input('accion', 'descargar');

            // 2. OBTENER PRODUCTOS CON STOCK BAJO (10 unidades o menos)
            $productos = Producto::stockBajo()
                ->orderBy('stock', 'asc')
                ->get();

            // 3. CALCULAR ESTADÍSTICAS
            $totalProductosBajo = $productos->count();
            $stockCritico = $productos->where('stock', '<=', 5)->count();
            $valorEnRiesgo = $productos->sum(function ($producto) {
                return floatval($producto->precio) * floatval($producto->stock);
            });

            // 4. GENERAR PDF
            $pdf = PDF::loadView('admin.reportes.stock-bajo', compact(
                'productos',
                'totalProductosBajo',
                'stockCritico',
                'valorEnRiesgo'
            ));

            // 5. CONFIGURAR PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable_html5_parser', true);
            $pdf->setOption('isRemoteEnabled', true);

            // 6. NOMBRE DEL ARCHIVO
            $nombreArchivo = 'reporte-stock-bajo-' . now()->format('Y-m-d-His') . '.pdf';

            // 7. RETORNAR SEGÚN ACCIÓN
            if ($accion === 'ver') {
                return $pdf->stream($nombreArchivo);
            } else {
                return $pdf->download($nombreArchivo);
            }
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en reporte de stock bajo: ' . $e->getMessage());
            Log::error('Línea: ' . $e->getLine());

            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }

    /**
     * Genera el reporte de ventas por rango de fechas en PDF
     * ✅ MÉTODO COMPLETAMENTE CORREGIDO
     */
    public function ventas(Request $request)
    {
        try {
            // ==========================================
            // 1. OBTENER PARÁMETROS DE LA SOLICITUD
            // ==========================================
            $accion = $request->input('accion', 'descargar');

            // Obtener fechas con valores por defecto
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            // Si no se proporcionan fechas, usar el mes actual
            if (!$fechaInicio) {
                $fechaInicio = now()->startOfMonth()->format('Y-m-d');
            }

            if (!$fechaFin) {
                $fechaFin = now()->format('Y-m-d');
            }

            // ==========================================
            // 2. OBTENER PEDIDOS CON FILTRO DE FECHAS
            // ==========================================
            $pedidos = Pedido::with(['cliente', 'productos'])
                ->whereBetween('fecha', [
                    $fechaInicio,
                    $fechaFin,
                ])
                ->orderBy('fecha', 'desc')
                ->orderBy('hora', 'desc')
                ->get();

            // ==========================================
            // 3. CALCULAR TOTALES DE FORMA SEGURA
            // ==========================================
            // ✅ CONVERSIÓN EXPLÍCITA A FLOAT
            $totalVentas = 0.0;
            foreach ($pedidos as $pedido) {
                $totalVentas = $totalVentas + floatval($pedido->total);
            }

            // ✅ CONVERSIÓN EXPLÍCITA A INT
            $cantidadPedidos = $pedidos->count();

            // ==========================================
            // 4. CALCULAR ESTADÍSTICAS ADICIONALES
            // ==========================================
            // Pedidos por estado
            $pedidosCompletados = $pedidos->where('estado', 'completado')->count();
            $pedidosPendientes = $pedidos->where('estado', 'pendiente')->count();
            $pedidosCancelados = $pedidos->where('estado', 'cancelado')->count();
            $pedidosProcesando = $pedidos->where('estado', 'procesando')->count();

            // Totales por estado
            $totalCompletados = 0.0;
            $totalPendientes = 0.0;
            $totalCancelados = 0.0;
            $totalProcesando = 0.0;

            foreach ($pedidos as $pedido) {
                $monto = floatval($pedido->total);

                if ($pedido->estado === 'completado') {
                    $totalCompletados += $monto;
                } elseif ($pedido->estado === 'pendiente') {
                    $totalPendientes += $monto;
                } elseif ($pedido->estado === 'procesando') {
                    $totalProcesando += $monto;
                } elseif ($pedido->estado === 'cancelado') {
                    $totalCancelados += $monto;
                }
            }

            // ==========================================
            // 5. PREPARAR DATOS PARA LA VISTA
            // ==========================================
            $datos = [
                // Datos principales
                'pedidos' => $pedidos,
                'totalVentas' => $totalVentas,
                'cantidadPedidos' => $cantidadPedidos,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,

                // Estadísticas por estado
                'pedidosCompletados' => $pedidosCompletados,
                'pedidosPendientes' => $pedidosPendientes,
                'pedidosCancelados' => $pedidosCancelados,
                'pedidosProcesando' => $pedidosProcesando,
                'totalCompletados' => $totalCompletados,
                'totalPendientes' => $totalPendientes,
                'totalCancelados' => $totalCancelados,
                'totalProcesando' => $totalProcesando,
            ];

            // ==========================================
            // 6. GENERAR PDF
            // ==========================================
            $pdf = PDF::loadView('admin.reportes.ventas', $datos);

            // 7. CONFIGURAR PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable_html5_parser', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('dpi', 150);

            // 8. NOMBRE DEL ARCHIVO
            $nombreArchivo = 'reporte-ventas-' . now()->format('Y-m-d-His') . '.pdf';

            // ==========================================
            // 9. RETORNAR PDF SEGÚN ACCIÓN
            // ==========================================
            if ($accion === 'ver') {
                return $pdf->stream($nombreArchivo);
            } else {
                return $pdf->download($nombreArchivo);
            }
        } catch (\Exception $e) {
            // ==========================================
            // 10. MANEJO DE ERRORES CON LOG DETALLADO
            // ==========================================
            Log::error('==========================================');
            Log::error('ERROR EN REPORTE DE VENTAS');
            Log::error('==========================================');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Línea: ' . $e->getLine());
            Log::error('Archivo: ' . $e->getFile());
            Log::error('Traza: ' . $e->getTraceAsString());
            Log::error('==========================================');

            return back()->with('error', 'Error al generar el reporte de ventas: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')');
        }
    }

    /**
     * Genera un reporte de ventas por cliente
     * (MÉTODO ADICIONAL OPCIONAL)
     */
    public function ventasPorCliente(Request $request)
    {
        try {
            $accion = $request->input('accion', 'descargar');
            $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

            // Obtener ventas agrupadas por cliente
            $ventasPorCliente = DB::table('pedidos')
                ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
                ->select(
                    'clientes.id',
                    'clientes.nombre',
                    'clientes.email',
                    'clientes.telefono',
                    DB::raw('COUNT(pedidos.id) as total_pedidos'),
                    DB::raw('SUM(pedidos.total) as total_ventas')
                )
                ->whereBetween('pedidos.fecha', [$fechaInicio, $fechaFin])
                ->groupBy('clientes.id', 'clientes.nombre', 'clientes.email', 'clientes.telefono')
                ->orderByDesc('total_ventas')
                ->get();

            $datos = [
                'ventasPorCliente' => $ventasPorCliente,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'totalClientes' => $ventasPorCliente->count(),
                'ventasGenerales' => $ventasPorCliente->sum('total_ventas')
            ];

            $pdf = PDF::loadView('admin.reportes.ventas-por-cliente', $datos);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable_html5_parser', true);

            $nombreArchivo = 'reporte-ventas-por-cliente-' . now()->format('Y-m-d-His') . '.pdf';

            if ($accion === 'ver') {
                return $pdf->stream($nombreArchivo);
            } else {
                return $pdf->download($nombreArchivo);
            }
        } catch (\Exception $e) {
            Log::error('Error en reporte de ventas por cliente: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }
}
