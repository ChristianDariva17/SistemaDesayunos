<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Stock\RegisterStockMovementAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductoRequest;
use App\Http\Requests\Admin\UpdateProductoRequest;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $stockBajo = Producto::stockMinimoBajo()->count();

        // ==========================================
        // PRODUCTOS PAGINADOS
        // ==========================================
        $productos = $query->latest()->paginate(10)->withQueryString();

        return view('admin.productos.index', compact(
            'productos',
            'totalProductos',
            'productosActivos',
            'productosNuevos',
            'stockBajo'
        ));
    }

    /**
     * ==========================================
     * MÉTODO: CREATE - FORMULARIO DE CREACIÓN
     * ==========================================
     */
    public function create()
    {
        return view('admin.productos.create');
    }

    /**
     * ==========================================
     * MÉTODO: STORE - GUARDAR NUEVO PRODUCTO
     * ==========================================
     * ✅ MEJORADO: Usa validación unificada + try-catch + transacciones
     */
    public function store(StoreProductoRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            // Manejar upload de imagen
            if ($request->hasFile('imagen')) {
                $validated['imagen'] = $this->handleImageUpload($request);
            }

            // Crear producto
            $producto = Producto::create($validated);

            DB::commit();

            // Log de auditoría
            Log::info("Producto creado: {$producto->nombre}", ['id' => $producto->id]);

            return redirect()->route('admin.productos.index')
                ->with('success', "✅ Producto '{$producto->nombre}' creado exitosamente");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al crear producto: " . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', '❌ Error al crear el producto. Por favor intenta nuevamente.');
        }
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

        return view('admin.productos.show', compact('producto'));
    }

    /**
     * ==========================================
     * MÉTODO: EDIT - FORMULARIO DE EDICIÓN
     * ==========================================
     */
    public function edit(Producto $producto)
    {
        return view('admin.productos.edit', compact('producto'));
    }

    /**
     * ==========================================
     * MÉTODO: UPDATE - ACTUALIZAR PRODUCTO
     * ==========================================
     * ✅ MEJORADO: Usa model binding consistente + validación unificada
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->getKey());

            $stockAnterior = (int) $producto->stock;

            // Manejar imagen solo si se subió una nueva
            if ($request->hasFile('imagen')) {
                $validated['imagen'] = $this->handleImageUpload($request, $producto);
            }

            // Actualizar producto
            $producto->update($validated);

            $this->registerManualStockAdjustment(
                producto: $producto,
                stockAnterior: $stockAnterior,
                stockNuevo: (int) $producto->stock,
                user: $request->user(),
                motivo: 'Manual product edit stock adjustment',
            );

            DB::commit();

            // Log de auditoría
            Log::info("Producto actualizado: {$producto->nombre}", ['id' => $producto->id]);

            return redirect()->route('admin.productos.show', $producto)
                ->with('success', "✅ Producto '{$producto->nombre}' actualizado correctamente");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar producto: " . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', '❌ Error al actualizar el producto. Por favor intenta nuevamente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: DESTROY - ELIMINAR PRODUCTO
     * ==========================================
     */
    public function destroy(Producto $producto)
    {
        try {
            $nombre = $producto->nombre;
            $productoId = $producto->id;

            // ==========================================
            // VALIDACIÓN: Verificar si tiene pedidos
            // ==========================================
            if ($producto->pedidos()->exists()) {
                $cantidadPedidos = $producto->pedidos()->count();
                return redirect()->back()
                    ->with('error', "❌ No se puede eliminar '{$nombre}' porque tiene {$cantidadPedidos} pedido(s) asociado(s).");
            }

            // Usar transacción
            DB::beginTransaction();

            // Eliminar la imagen si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }

            // Eliminar el producto
            $producto->delete();

            // Confirmar transacción
            DB::commit();

            // Log de auditoría
            Log::warning('Producto eliminado', [
                'producto_id' => $productoId,
                'nombre' => $nombre,
                'usuario' => auth()->id() ?? 'Sistema'
            ]);

            return redirect()->route('admin.productos.index')
                ->with('success', "🗑️ Producto '{$nombre}' eliminado correctamente");

        } catch (Exception $e) {
            // Revertir transacción
            DB::rollBack();

            // Registrar el error
            Log::error('Error al eliminar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al eliminar el producto. Por favor, intenta nuevamente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO PRIVADO: MANEJAR SUBIDA DE IMAGEN
     * ==========================================
     * ✅ MEJORADO: Retorna string en lugar de ?string
     */
    private function handleImageUpload(Request $request, ?Producto $producto = null): ?string
    {
        if (!$request->hasFile('imagen')) {
            return null;
        }

        // Si hay un producto existente y tiene imagen, eliminarla
        if ($producto && $producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
        }

        // Guardar nueva imagen
        return $request->file('imagen')->store('productos', 'public');
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
     * MÉTODO: CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
     * ==========================================
     * Ruta: PATCH /productos/{producto}/toggle-estado
     */
    public function toggleEstado(Producto $producto)
    {
        try {
            $estadoAnterior = $producto->estado;
            $nuevoEstado = $producto->estado === 'activo' ? 'inactivo' : 'activo';
            
            $producto->update(['estado' => $nuevoEstado]);

            Log::info('Estado de producto cambiado', [
                'producto_id' => $producto->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado
            ]);

            return response()->json([
                'success' => true,
                'message' => "Estado cambiado a: " . ucfirst($nuevoEstado),
                'nuevo_estado' => $nuevoEstado
            ]);
        } catch (Exception $e) {
            Log::error('Error al cambiar estado de producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado'
            ], 500);
        }
    }

    /**
     * ==========================================
     * MÉTODO: ACTUALIZAR STOCK
     * ==========================================
     * Ruta: PATCH /productos/{producto}/actualizar-stock
     */
    public function actualizarStock(Request $request, Producto $producto)
    {
        try {
            $validated = $request->validate([
                'cantidad' => 'required|integer|min:0',
                'tipo' => 'required|in:incrementar,decrementar,establecer',
                'motivo' => 'nullable|string|max:255'
            ]);

            DB::beginTransaction();

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->getKey());

            $stockAnterior = (int) $producto->stock;
            $stockNuevo = $stockAnterior;

            switch ($validated['tipo']) {
                case 'incrementar':
                    $stockNuevo += (int) $validated['cantidad'];
                    break;

                case 'decrementar':
                    if ($stockAnterior < (int) $validated['cantidad']) {
                        DB::rollBack();

                        return redirect()->back()
                            ->with('error', '❌ Stock insuficiente para realizar esta operación.');
                    }
                    $stockNuevo -= (int) $validated['cantidad'];
                    break;

                case 'establecer':
                    $stockNuevo = (int) $validated['cantidad'];
                    break;
            }

            $producto->stock = $stockNuevo;
            $producto->save();

            $this->registerManualStockAdjustment(
                producto: $producto,
                stockAnterior: (int) $stockAnterior,
                stockNuevo: (int) $producto->stock,
                user: $request->user(),
                motivo: $validated['motivo'] ?? 'Manual stock adjustment',
            );

            DB::commit();

            Log::info('Stock actualizado', [
                'producto_id' => $producto->id,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $producto->stock,
                'tipo' => $validated['tipo'],
                'motivo' => $validated['motivo'] ?? 'No especificado'
            ]);

            return redirect()->back()
                ->with('success', "✅ Stock actualizado correctamente. Anterior: {$stockAnterior}, Nuevo: {$producto->stock}");

        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Error al actualizar stock', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al actualizar el stock.');
        }
    }

    private function registerManualStockAdjustment(
        Producto $producto,
        int $stockAnterior,
        int $stockNuevo,
        ?User $user,
        ?string $motivo = null,
    ): void {
        if ($stockAnterior === $stockNuevo) {
            return;
        }

        app(RegisterStockMovementAction::class)->handle(
            producto: $producto,
            tipo: StockMovimiento::TIPO_AJUSTE,
            cantidad: abs($stockNuevo - $stockAnterior),
            stockAnterior: $stockAnterior,
            stockNuevo: $stockNuevo,
            user: $user,
            motivo: $motivo ?: 'Manual stock adjustment',
        );
    }

    /**
     * ==========================================
     * MÉTODO: DUPLICAR PRODUCTO
     * ==========================================
     * Ruta: POST /productos/{producto}/duplicar
     */
    public function duplicar(Producto $producto)
    {
        try {
            DB::beginTransaction();

            // Crear copia del producto
            $nuevoProducto = $producto->replicate();
            $nuevoProducto->nombre = $producto->nombre . ' (Copia)';
            $nuevoProducto->codigo_barras = null; // Limpiar campos únicos
            $nuevoProducto->sku = null;
            $nuevoProducto->stock = 0; // Reset stock

            // Copiar imagen si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                $extension = pathinfo($producto->imagen, PATHINFO_EXTENSION);
                $nuevoNombre = 'productos/' . uniqid() . '.' . $extension;
                Storage::disk('public')->copy($producto->imagen, $nuevoNombre);
                $nuevoProducto->imagen = $nuevoNombre;
            }

            $nuevoProducto->save();

            DB::commit();

            Log::info('Producto duplicado', [
                'producto_original' => $producto->id,
                'producto_nuevo' => $nuevoProducto->id
            ]);

            return redirect()->route('admin.productos.edit', $nuevoProducto)
                ->with('success', "✅ Producto duplicado correctamente. Edita los detalles necesarios.");

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al duplicar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al duplicar el producto.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: EXPORTAR A CSV
     * ==========================================
     * Ruta: GET /productos/exportar
     */
    public function exportar(Request $request)
    {
        try {
            $query = Producto::query();

            // Aplicar los mismos filtros que en index()
            if ($request->filled('search')) {
                $query->where('nombre', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            $productos = $query->orderBy('nombre')->get();

            // Nombre del archivo
            $filename = 'productos_' . now()->format('Y-m-d_His') . '.csv';

            // Headers para descarga
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            // Crear CSV
            $callback = function () use ($productos) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Encabezados
                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Descripción',
                    'Categoría',
                    'Precio',
                    'Stock',
                    'Código de Barras',
                    'SKU',
                    'Estado',
                    'Fecha de Creación'
                ]);

                // Datos
                foreach ($productos as $producto) {
                    fputcsv($file, [
                        $producto->id,
                        $producto->nombre,
                        $producto->descripcion,
                        $producto->categoria,
                        $producto->precio,
                        $producto->stock,
                        $producto->codigo_barras,
                        $producto->sku,
                        ucfirst($producto->estado),
                        $producto->created_at->format('d/m/Y H:i:s')
                    ]);
                }

                fclose($file);
            };

            Log::info('Productos exportados', [
                'total' => $productos->count(),
                'usuario' => auth()->id() ?? 'Sistema'
            ]);

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('Error al exportar productos', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al exportar los productos.');
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
