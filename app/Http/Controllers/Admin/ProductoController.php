<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Producto\ToggleProductoStatusAction;
use App\Actions\Stock\RegisterStockAdjustmentAction;
use App\Enums\ProductoEstado;
use App\Enums\StockAdjustmentOperation;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductoRequest;
use App\Http\Requests\Admin\UpdateProductoRequest;
use App\Models\Producto;
use App\Queries\ProductoQuery;
use App\Services\ProductImageService;
use App\Support\InventoryLimits;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductImageService $productImageService,
        private readonly RegisterStockAdjustmentAction $registerStockAdjustment,
    ) {}

    /**
     * ==========================================
     * MÉTODO: INDEX - LISTADO DE PRODUCTOS
     * ==========================================
     * Muestra todos los productos con filtros, búsqueda y estadísticas
     */
    public function index(Request $request, ProductoQuery $productoQuery)
    {
        Gate::authorize('viewAny', Producto::class);

        // ==========================================
        // ESTADÍSTICAS DEL DASHBOARD
        // ==========================================
        $totalProductos = Producto::count();
        $productosActivos = Producto::activos()->count();
        $productosNuevos = Producto::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $stockBajo = Producto::stockMinimoBajo()->count();

        // ==========================================
        // PRODUCTOS PAGINADOS
        // ==========================================
        $productos = $productoQuery->paginate($request);

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
        Gate::authorize('create', Producto::class);

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
        $newImagePath = null;

        try {
            $validated = $request->validated();

            DB::beginTransaction();

            if ($request->hasFile('imagen')) {
                $newImagePath = $this->handleImageUpload($request);
                $validated['imagen'] = $newImagePath;
            }

            // Crear producto
            $producto = Producto::create($validated);

            DB::commit();

            // Log de auditoría
            Log::info("Producto creado: {$producto->nombre}", ['id' => $producto->id]);

            return redirect()->route('admin.productos.index')
                ->with('success', "✅ Producto '{$producto->nombre}' creado exitosamente");

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->cleanupImage($newImagePath, 'create compensation');
            Log::error('Error al crear producto: '.$e->getMessage());

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
        Gate::authorize('view', $producto);

        // Cargar relaciones con pedidos
        $producto->loadCount('pedidos');
        $producto->load(['pedidos' => function ($query) {
            $query->with('cliente')->latest('pedidos.created_at')->take(5);
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
        Gate::authorize('update', $producto);

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
        $newImagePath = null;
        $oldImagePath = null;
        $shouldDeleteOldImage = false;

        try {
            $validated = $request->validated();
            $removeImage = (bool) ($validated['eliminar_imagen'] ?? false);
            $stockNuevo = (int) $validated['stock'];
            unset($validated['eliminar_imagen'], $validated['stock']);

            DB::beginTransaction();

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->getKey());

            $oldImagePath = $producto->imagen;

            if ($request->hasFile('imagen')) {
                $newImagePath = $this->handleImageUpload($request);
                $validated['imagen'] = $newImagePath;
                $shouldDeleteOldImage = true;
            } elseif ($removeImage) {
                $validated['imagen'] = null;
                $shouldDeleteOldImage = true;
            }

            // Actualizar producto
            $producto->update($validated);

            $stockAdjustment = $this->registerStockAdjustment->handle(
                productoId: (int) $producto->getKey(),
                operation: StockAdjustmentOperation::Set,
                quantity: $stockNuevo,
                user: $request->user(),
                motivo: 'Manual product edit stock adjustment',
            );
            $producto = $stockAdjustment->producto;

            DB::commit();

            if ($shouldDeleteOldImage && $oldImagePath !== $newImagePath) {
                $this->cleanupImage($oldImagePath, 'post-update cleanup', ['producto_id' => $producto->id]);
            }

            // Log de auditoría
            Log::info("Producto actualizado: {$producto->nombre}", ['id' => $producto->id]);

            return redirect()->route('admin.productos.show', $producto)
                ->with('success', "✅ Producto '{$producto->nombre}' actualizado correctamente");

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->cleanupImage($newImagePath, 'update compensation', ['producto_id' => $producto->id]);
            Log::error('Error al actualizar producto: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', '❌ Error al actualizar el producto. Por favor intenta nuevamente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO PRIVADO: MANEJAR SUBIDA DE IMAGEN
     * ==========================================
     */
    private function handleImageUpload(Request $request): string
    {
        $image = $request->file('imagen');

        if (! $image instanceof UploadedFile) {
            throw new RuntimeException('The uploaded product image is invalid.');
        }

        return $this->productImageService->store($image);
    }

    /**
     * ==========================================
     * MÉTODO: DESTROY - ELIMINAR PRODUCTO
     * ==========================================
     */
    public function destroy(Producto $producto)
    {
        Gate::authorize('delete', $producto);

        try {
            $nombre = $producto->nombre;
            $productoId = $producto->id;
            $imagePath = $producto->imagen;

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

            // Eliminar el producto
            $producto->delete();

            // Confirmar transacción
            DB::commit();

            $this->cleanupImage($imagePath, 'post-destroy cleanup', ['producto_id' => $productoId]);

            // Log de auditoría
            Log::warning('Producto eliminado', [
                'producto_id' => $productoId,
                'nombre' => $nombre,
                'usuario' => auth()->id() ?? 'Sistema',
            ]);

            return redirect()->route('admin.productos.index')
                ->with('success', "🗑️ Producto '{$nombre}' eliminado correctamente");

        } catch (Throwable $e) {
            // Revertir transacción
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Registrar el error
            Log::error('Error al eliminar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al eliminar el producto. Por favor, intenta nuevamente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: BÚSQUEDA AVANZADA CON AJAX
     * ==========================================
     * Ruta: GET /productos/buscar?q=termino
     */
    public function buscar(Request $request)
    {
        Gate::authorize('viewAny', Producto::class);

        try {
            $termino = $request->get('q', '');

            $productos = Producto::where('nombre', 'like', '%'.$termino.'%')
                ->activos()
                ->limit(10)
                ->get(['id', 'nombre', 'precio', 'stock', 'imagen']);

            return response()->json([
                'success' => true,
                'productos' => $productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'precio' => number_format((float) $producto->precio, 2),
                        'stock' => $producto->stock,
                        'imagen_url' => $producto->getImagenThumbnailUrl(),
                    ];
                }),
            ]);
        } catch (Exception $e) {
            Log::error('Error en búsqueda de productos', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos',
            ], 500);
        }
    }

    /**
     * ==========================================
     * MÉTODO: CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
     * ==========================================
     * Ruta: PATCH /productos/{producto}/toggle-estado
     */
    public function toggleEstado(Producto $producto, ToggleProductoStatusAction $toggleProductoStatusAction)
    {
        Gate::authorize('toggleStatus', $producto);

        try {
            $producto = $toggleProductoStatusAction->handle($producto);
            $nuevoEstado = (string) $producto->estado;

            return response()->json([
                'success' => true,
                'message' => 'Estado cambiado a: '.ucfirst($nuevoEstado),
                'nuevo_estado' => $nuevoEstado,
            ]);
        } catch (Exception $e) {
            Log::error('Error al cambiar estado de producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado',
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
        Gate::forUser($request->user())->authorize('updateStock', $producto);

        if (is_string($request->input('motivo'))) {
            $request->merge([
                'motivo' => $this->normalizeManualStockMotivo($request->input('motivo')),
            ]);
        }

        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0', 'max:'.InventoryLimits::MAX_STOCK_LEVEL],
            'tipo' => ['required', 'in:incrementar,decrementar,establecer'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);
        $motivo = $this->normalizeManualStockMotivo($validated['motivo'] ?? null);
        $operation = match ($validated['tipo']) {
            'incrementar' => StockAdjustmentOperation::Increment,
            'decrementar' => StockAdjustmentOperation::Decrement,
            'establecer' => StockAdjustmentOperation::Set,
            default => throw ValidationException::withMessages([
                'tipo' => 'El tipo de ajuste de stock no es válido.',
            ]),
        };

        try {
            $result = $this->registerStockAdjustment->handle(
                productoId: (int) $producto->getKey(),
                operation: $operation,
                quantity: (int) $validated['cantidad'],
                user: $request->user(),
                motivo: $motivo,
            );

            Log::info('Stock actualizado', [
                'producto_id' => $result->producto->id,
                'stock_anterior' => $result->stockAnterior,
                'stock_nuevo' => $result->stockNuevo,
                'tipo' => $validated['tipo'],
                'motivo' => $motivo ?? 'No especificado',
            ]);

            return redirect()->back()
                ->with('success', "✅ Stock actualizado correctamente. Anterior: {$result->stockAnterior}, Nuevo: {$result->stockNuevo}");

        } catch (InsufficientStockException) {
            return redirect()->back()
                ->with('error', '❌ Stock insuficiente para realizar esta operación.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {

            Log::error('Error al actualizar stock', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al actualizar el stock.');
        }
    }

    private function normalizeManualStockMotivo(mixed $motivo): ?string
    {
        if (! is_string($motivo)) {
            return null;
        }

        $normalized = preg_replace('/^[\s\p{Z}\x{FEFF}]+|[\s\p{Z}\x{FEFF}]+$/u', '', $motivo);
        $normalized = $normalized ?? $motivo;

        return $normalized === '' ? null : $normalized;
    }

    /**
     * ==========================================
     * MÉTODO: DUPLICAR PRODUCTO
     * ==========================================
     * Ruta: POST /productos/{producto}/duplicar
     */
    public function duplicar(Producto $producto)
    {
        Gate::authorize('duplicate', $producto);
        $copiedImagePath = null;

        try {
            DB::beginTransaction();

            // Crear copia del producto
            $nuevoProducto = $producto->replicate();
            $nuevoProducto->nombre = $producto->nombre.' (Copia)';
            $nuevoProducto->codigo_barras = null; // Limpiar campos únicos
            $nuevoProducto->sku = null;
            $nuevoProducto->stock = 0; // Reset stock

            $copiedImagePath = $this->productImageService->copy($producto->imagen);
            $nuevoProducto->imagen = $copiedImagePath;

            $nuevoProducto->save();

            DB::commit();

            Log::info('Producto duplicado', [
                'producto_original' => $producto->id,
                'producto_nuevo' => $nuevoProducto->id,
            ]);

            return redirect()->route('admin.productos.edit', $nuevoProducto)
                ->with('success', '✅ Producto duplicado correctamente. Edita los detalles necesarios.');

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->cleanupImage($copiedImagePath, 'duplicate compensation', ['producto_id' => $producto->id]);

            Log::error('Error al duplicar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al duplicar el producto.');
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function cleanupImage(?string $path, string $operation, array $context = []): void
    {
        try {
            $this->productImageService->delete($path);
        } catch (Throwable $e) {
            Log::warning('Product image cleanup failed', $context + [
                'operation' => $operation,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
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
        Gate::authorize('export', Producto::class);

        try {
            // Nombre del archivo
            $filename = 'productos_'.now()->format('Y-m-d_His').'.csv';
            $chunkSize = max(1, (int) config('reportes.csv_chunk_size', 500));

            // Headers para descarga
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            // Crear CSV
            $callback = function () use ($request, $chunkSize) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

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
                    'Fecha de Creación',
                ]);

                $query = Producto::query();

                // Aplicar los mismos filtros que en index()
                if ($request->filled('search')) {
                    $query->where('nombre', 'like', '%'.$request->search.'%');
                }

                if ($request->filled('categoria')) {
                    $query->where('categoria', $request->categoria);
                }

                if ($request->filled('estado')) {
                    $query->where('estado', $request->estado);
                }

                if ($request->string('stock')->toString() === 'bajo') {
                    $query->stockMinimoBajo();
                }

                $total = 0;

                $query->orderBy('nombre')
                    ->orderBy('id')
                    ->chunk($chunkSize, function ($productos) use ($file, &$total): void {
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
                                $producto->created_at->format('d/m/Y H:i:s'),
                            ]);
                        }

                        $total += $productos->count();
                    });

                fclose($file);

                Log::info('Productos exportados', [
                    'total' => $total,
                    'usuario' => auth()->id() ?? 'Sistema',
                ]);
            };

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('Error al exportar productos', [
                'error' => $e->getMessage(),
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
        Gate::authorize('viewAny', Producto::class);

        try {
            $estadisticas = [
                'total' => Producto::count(),
                'activos' => Producto::activos()->count(),
                'inactivos' => Producto::where('estado', ProductoEstado::Inactive->value)->count(),
                'stock_bajo' => Producto::stockBajo()->count(),
                'sin_stock' => Producto::where('stock', 0)->count(),
                'valor_inventario' => Producto::activos()->sum(DB::raw('precio * stock')),
                'precio_promedio' => Producto::activos()->avg('precio'),
                'stock_promedio' => Producto::activos()->avg('stock'),
                'por_categoria' => Producto::selectRaw('categoria, COUNT(*) as total')
                    ->whereNotNull('categoria')
                    ->groupBy('categoria')
                    ->orderBy('total', 'desc')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas,
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
            ], 500);
        }
    }
}
