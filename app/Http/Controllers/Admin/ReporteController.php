<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductoEstado;
use App\Enums\StockMovimientoTipo;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Services\Reporting\DashboardSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReporteController extends Controller
{
    /**
     * Muestra la vista principal de reportes
     */
    public function index(DashboardSummaryService $dashboardSummary)
    {
        try {
            $summary = $dashboardSummary->summary();
            $inventoryStats = Cache::remember(
                'reporting.inventory-summary.v1',
                now()->addSeconds(60),
                fn (): array => [
                    'stockTotal' => (int) Producto::query()->sum('stock'),
                    'stockBajo' => Producto::stockMinimoBajo()->count(),
                    'valorInventario' => DB::table('productos')
                        ->selectRaw('SUM(precio * stock) as total')
                        ->value('total') ?? 0,
                ],
            );

            $pedidosProcesando = Pedido::query()
                ->where('estado', 'procesando')
                ->count();

            // Preparar array
            $estadisticas = [
                'totalProductos' => $summary['totalProductos'],
                'productosActivos' => $summary['productosActivos'],
                'stockTotal' => $inventoryStats['stockTotal'],
                'stockBajo' => $inventoryStats['stockBajo'],
                'valorInventario' => $inventoryStats['valorInventario'],
                'totalClientes' => $summary['totalClientes'],
                'clientesActivos' => $summary['clientesActivos'],
                'totalPedidos' => $summary['totalPedidos'],
                'pedidosCompletados' => $summary['pedidosCompletados'],
                'pedidosPendientes' => $summary['pedidosPendientes'],
                'pedidosProcesando' => $pedidosProcesando,
                'totalVentas' => $summary['totalVentas'],
                'ventasMesActual' => $summary['ventasMes'],
                'totalEmpleados' => $summary['totalEmpleados'],
            ];

            return view('admin.reportes.index', compact('estadisticas'));
        } catch (\Exception $e) {
            Log::error('Error al cargar estadísticas: '.$e->getMessage());

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
                'valorInventario',
            ));

            // 6. CONFIGURAR PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable_html5_parser', true);
            $pdf->setOption('isRemoteEnabled', true);

            // 7. NOMBRE DEL ARCHIVO
            $nombreArchivo = 'reporte-inventario-'.now()->format('Y-m-d-His').'.pdf';

            // 8. RETORNAR SEGÚN ACCIÓN
            if ($accion === 'ver') {
                return $pdf->stream($nombreArchivo);
            } else {
                return $pdf->download($nombreArchivo);
            }
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en reporte de inventario: '.$e->getMessage());
            Log::error('Línea: '.$e->getLine());

            return back()->with('error', 'Error al generar el reporte: '.$e->getMessage());
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

            // 2. OBTENER PRODUCTOS CON STOCK BAJO SEGUN SU MINIMO CONFIGURADO
            $productos = Producto::stockMinimoBajo()
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
                'valorEnRiesgo',
            ));

            // 5. CONFIGURAR PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable_html5_parser', true);
            $pdf->setOption('isRemoteEnabled', true);

            // 6. NOMBRE DEL ARCHIVO
            $nombreArchivo = 'reporte-stock-bajo-'.now()->format('Y-m-d-His').'.pdf';

            // 7. RETORNAR SEGÚN ACCIÓN
            if ($accion === 'ver') {
                return $pdf->stream($nombreArchivo);
            } else {
                return $pdf->download($nombreArchivo);
            }
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en reporte de stock bajo: '.$e->getMessage());
            Log::error('Línea: '.$e->getLine());

            return back()->with('error', 'Error al generar el reporte: '.$e->getMessage());
        }
    }

    /**
     * Muestra el reporte filtrable de movimientos de stock.
     */
    public function stockMovimientos(Request $request)
    {
        $rules = [
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'tipo' => ['nullable', Rule::enum(StockMovimientoTipo::class)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d'],
        ];

        if ($request->filled('fecha_inicio')) {
            $rules['fecha_fin'][] = 'after_or_equal:fecha_inicio';
        }

        $validated = $request->validate($rules);

        $query = StockMovimiento::query()
            ->with(['producto', 'pedido', 'user']);

        if (! empty($validated['producto_id'])) {
            $query->where('producto_id', (int) $validated['producto_id']);
        }

        if (! empty($validated['tipo'])) {
            $query->where('tipo', $validated['tipo']);
        }

        if (! empty($validated['user_id'])) {
            $query->where('user_id', (int) $validated['user_id']);
        }

        if (! empty($validated['fecha_inicio'])) {
            $query->where('created_at', '>=', CarbonImmutable::parse($validated['fecha_inicio'])->startOfDay());
        }

        if (! empty($validated['fecha_fin'])) {
            $query->where('created_at', '<=', CarbonImmutable::parse($validated['fecha_fin'])->endOfDay());
        }

        $movimientos = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $productos = Producto::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $usuarios = User::query()
            ->whereIn('id', StockMovimiento::query()
                ->select('user_id')
                ->whereNotNull('user_id'),
            )
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $tipos = StockMovimientoTipo::cases();

        return view('admin.reportes.stock-movimientos', compact(
            'movimientos',
            'productos',
            'usuarios',
            'tipos',
        ));
    }

    /**
     * Muestra el resumen de inventario por producto.
     */
    public function resumenInventario(Request $request)
    {
        $validated = $request->validate([
            'buscar' => ['nullable', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', Rule::enum(ProductoEstado::class)],
        ]);

        $latestMovementDate = StockMovimiento::query()
            ->select('created_at')
            ->whereColumn('stock_movimientos.producto_id', 'productos.id')
            ->latest('created_at')
            ->latest('id')
            ->limit(1);

        $latestMovementType = StockMovimiento::query()
            ->select('tipo')
            ->whereColumn('stock_movimientos.producto_id', 'productos.id')
            ->latest('created_at')
            ->latest('id')
            ->limit(1);

        $query = Producto::query()
            ->select('productos.*')
            ->selectSub($latestMovementDate, 'ultimo_movimiento_fecha')
            ->selectSub($latestMovementType, 'ultimo_movimiento_tipo')
            ->withSum([
                'stockMovimientos as total_entradas' => fn ($query) => $query->where('tipo', StockMovimientoTipo::Entry->value),
            ], 'cantidad')
            ->withSum([
                'stockMovimientos as total_salidas' => fn ($query) => $query->where('tipo', StockMovimientoTipo::Exit->value),
            ], 'cantidad')
            ->withSum([
                'stockMovimientos as total_ajustes' => fn ($query) => $query->where('tipo', StockMovimientoTipo::Adjustment->value),
            ], 'cantidad');

        if (! empty($validated['buscar'])) {
            $query->where('nombre', 'like', '%'.$validated['buscar'].'%');
        }

        if (! empty($validated['categoria'])) {
            $query->where('categoria', $validated['categoria']);
        }

        if (! empty($validated['estado'])) {
            $query->where('estado', $validated['estado']);
        }

        $productos = $query
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $categorias = Producto::query()
            ->select('categoria')
            ->whereNotNull('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return view('admin.reportes.resumen-inventario', compact('productos', 'categorias'));
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
            if (! $fechaInicio) {
                $fechaInicio = now()->startOfMonth()->format('Y-m-d');
            }

            if (! $fechaFin) {
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
            $nombreArchivo = 'reporte-ventas-'.now()->format('Y-m-d-His').'.pdf';

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
            Log::error('Mensaje: '.$e->getMessage());
            Log::error('Línea: '.$e->getLine());
            Log::error('Archivo: '.$e->getFile());
            Log::error('Traza: '.$e->getTraceAsString());
            Log::error('==========================================');

            return back()->with('error', 'Error al generar el reporte de ventas: '.$e->getMessage().' (Línea: '.$e->getLine().')');
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
                    DB::raw('SUM(pedidos.total) as total_ventas'),
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
                'ventasGenerales' => $ventasPorCliente->sum('total_ventas'),
            ];

            $pdf = PDF::loadView('admin.reportes.ventas-por-cliente', $datos);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable_html5_parser', true);

            $nombreArchivo = 'reporte-ventas-por-cliente-'.now()->format('Y-m-d-His').'.pdf';

            if ($accion === 'ver') {
                return $pdf->stream($nombreArchivo);
            } else {
                return $pdf->download($nombreArchivo);
            }
        } catch (\Exception $e) {
            Log::error('Error en reporte de ventas por cliente: '.$e->getMessage());

            return back()->with('error', 'Error al generar el reporte: '.$e->getMessage());
        }
    }
}
