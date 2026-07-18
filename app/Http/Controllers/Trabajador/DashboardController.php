<?php

namespace App\Http\Controllers\Trabajador;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\Reporting\DashboardSummaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    public function index(DashboardSummaryService $dashboardSummary)
    {
        try {
            $summary = $dashboardSummary->summary();

            // ==========================================
            // PEDIDOS DEL TRABAJADOR ACTUAL
            // ==========================================
            // Nota: Si tienes un campo 'created_by' en pedidos,
            // puedes filtrar solo los pedidos creados por este trabajador
            $misPedidos = Pedido::with('cliente')
                ->latest()
                ->take(10)
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
            return view('trabajador.dashboard', array_merge($summary, [
                'misPedidos' => $misPedidos,
                'ultimosPedidos' => $ultimosPedidos,
                'trabajador' => $trabajador,
            ]));

        } catch (\Exception $e) {
            Log::error('==========================================');
            Log::error('ERROR EN DASHBOARD TRABAJADOR');
            Log::error('==========================================');
            Log::error('Mensaje: '.$e->getMessage());
            Log::error('Línea: '.$e->getLine());
            Log::error('Archivo: '.$e->getFile());

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
                'trabajador' => Auth::user(),
            ])->with('error', 'Error al cargar el dashboard. Contacte con el administrador.');
        }
    }
}
