<?php

namespace App\Http\Controllers\Trabajador;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductoController extends Controller
{
    /**
     * ==========================================
     * MÉTODO: INDEX - LISTADO DE PRODUCTOS
     * ==========================================
     * Muestra todos los productos con filtros, búsqueda y estadísticas
     */
    public function index(Request $request)
    {
        $query = Producto::query();

        // ==========================================
        // FILTRO: BÚSQUEDA POR NOMBRE
        // ==========================================
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        // ==========================================
        // FILTRO: POR CATEGORÍA
        // ==========================================
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // ==========================================
        // FILTRO: POR ESTADO (activo/inactivo)
        // ==========================================
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // ==========================================
        // ESTADÍSTICAS DEL DASHBOARD
        // ==========================================
        $totalProductos = Producto::count();
        $productosActivos = Producto::where('estado', 'activo')->count();
        $productosNuevos = Producto::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $stockBajo = Producto::stockBajo()->count();

        // ==========================================
        // PRODUCTOS PAGINADOS
        // ==========================================
        $productos = $query->latest()->paginate(10)->withQueryString();

        return view('trabajador.productos.index', compact(
            'productos',
            'totalProductos',
            'productosActivos',
            'productosNuevos',
            'stockBajo'
        ));
    }

    /**
     * ==========================================
     * MÉTODO: SHOW - VER DETALLE DEL PRODUCTO
     * ==========================================
     */
    public function show(Producto $producto)
    {
        // Cargar relaciones con pedidos
        $producto->loadCount('pedidos');
        $producto->load(['pedidos' => function ($query) {
            $query->latest()->take(5);
        }]);

        // Calcular total vendido (opcional)
        $producto->total_vendido = $producto->pedidos->sum('total');

        return view('trabajador.productos.show', compact('producto'));
    }

    /**
     * ==========================================
     * MÉTODO: BÚSQUEDA AVANZADA CON AJAX
     * ==========================================
     * Ruta: GET /productos/buscar?q=termino
     */
    public function buscar(Request $request)
    {
        try {
            $termino = $request->get('q', '');
            
            $productos = Producto::where('nombre', 'like', '%' . $termino . '%')
                ->where('estado', 'activo')
                ->limit(10)
                ->get(['id', 'nombre', 'precio', 'stock', 'imagen']);

            return response()->json([
                'success' => true,
                'productos' => $productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'precio' => number_format($producto->precio, 2),
                        'stock' => $producto->stock,
                        'imagen_url' => $producto->imagen ? asset('storage/' . $producto->imagen) : null,
                    ];
                })
            ]);
        } catch (Exception $e) {
            Log::error('Error en búsqueda de productos', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos'
            ], 500);
        }
    }


    /**
     * ==========================================
     * MÉTODO: OBTENER ESTADÍSTICAS DETALLADAS
     * ==========================================
     * Ruta: GET /productos/estadisticas (API JSON)
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total' => Producto::count(),
                'activos' => Producto::where('estado', 'activo')->count(),
                'inactivos' => Producto::where('estado', 'inactivo')->count(),
                'stock_bajo' => Producto::stockBajo()->count(),
                'sin_stock' => Producto::where('stock', 0)->count(),
                'valor_inventario' => Producto::where('estado', 'activo')->sum(DB::raw('precio * stock')),
                'precio_promedio' => Producto::where('estado', 'activo')->avg('precio'),
                'stock_promedio' => Producto::where('estado', 'activo')->avg('stock'),
                'por_categoria' => Producto::selectRaw('categoria, COUNT(*) as total')
                    ->whereNotNull('categoria')
                    ->groupBy('categoria')
                    ->orderBy('total', 'desc')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}
