<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\Reporting\DashboardSummaryService;

class DashboardController extends Controller
{
    public function index(DashboardSummaryService $dashboardSummary)
    {
        $summary = $dashboardSummary->summary();
        
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
        return view('admin.dashboard', array_merge($summary, [
            'ultimosPedidos' => $ultimosPedidos,
        ]));
    }
}
