<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Pedido\CreatePedidoAction;
use App\Actions\Pedido\UpdatePedidoAction;
use App\Actions\Stock\RegisterStockMovementAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pedido\StorePedidoRequest;
use App\Http\Requests\Pedido\UpdatePedidoRequest;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\StockMovimiento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PedidoController extends Controller
{
    /**
     * Mostrar listado de pedidos con filtros y estadísticas
     */
    public function index(Request $request)
    {
        $estadisticas = [
            'total_pedidos' => Pedido::count(),
            'pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'completados' => Pedido::where('estado', 'completado')->count(),
            'ventas_hoy' => Pedido::whereDate('fecha', today())
                ->where('estado', 'completado')
                ->sum('total'),
            'ventas_mes' => Pedido::whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->where('estado', 'completado')
                ->sum('total'),
        ];

        // Load canonical employee fields.
        $query = Pedido::query()->with(['empleado:id,nombre,rol_operativo', 'cliente:id,nombre,apellido']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('numero_pedido', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($q2) use ($search) {
                        $q2->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->get('estado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->get('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->get('fecha_hasta'));
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->get('fecha'));
        }

        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->get('empleado_id'));
        }

        $pedidos = $query->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(15)
            ->withQueryString();

        $empleados = Empleado::where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'rol_operativo']);

        return view('admin.pedidos.index', compact('pedidos', 'estadisticas', 'empleados'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $productos = Producto::where('estado', 'activo')
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio', 'stock', 'imagen']);

        // Use canonical employee fields.
        $empleados = Empleado::where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'rol_operativo']);

        $clientes = Cliente::where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'email']);

        $cliente_seleccionado = null;
        if ($request->filled('cliente_id')) {
            $cliente_seleccionado = Cliente::find($request->get('cliente_id'));
        }

        return view('admin.pedidos.create', compact('productos', 'empleados', 'clientes', 'cliente_seleccionado'));
    }

    /**
     * Guardar nuevo pedido
     */
    public function store(StorePedidoRequest $request, CreatePedidoAction $createPedidoAction)
    {
        $validated = $request->validated();

        try {
            $pedido = $createPedidoAction->handle($validated, auth()->id());

            return redirect()->route('admin.pedidos.show', $pedido)
                ->with('success', "✅ Pedido {$pedido->numero_pedido} creado correctamente");
        } catch (Exception $e) {
            Log::error('Error al crear pedido', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles del pedido
     */
    public function show(Pedido $pedido)
    {
        $pedido->load([
            'cliente',
            'empleado:id,nombre,rol_operativo,estado',
            'productos' => function ($query) {
                $query->select('productos.id', 'productos.nombre', 'productos.imagen')
                    ->withPivot('cantidad', 'precio_unitario', 'subtotal');
            }
        ]);

        return view('admin.pedidos.show', compact('pedido'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Pedido $pedido)
    {
        // ✅ Cargar relaciones con validación
        $pedido->load(['cliente', 'empleado', 'productos']);

        // Obtener listas completas
        $clientes = Cliente::orderBy('nombre')->get();
        $empleados = Empleado::where('estado', 'activo')->orderBy('nombre')->get();
        $productos = Producto::where('stock', '>', 0)->orderBy('nombre')->get();

        return view('admin.pedidos.edit', compact('pedido', 'clientes', 'empleados', 'productos'));
    }

    /**
     * Actualizar pedido
     */
    public function update(UpdatePedidoRequest $request, Pedido $pedido, UpdatePedidoAction $updatePedidoAction)
    {
        $validated = $request->validated();

        try {
            $updatePedidoAction->handle($validated, $pedido, auth()->id());

            return redirect()->route('admin.pedidos.show', $pedido)
                ->with('success', "✅ Pedido actualizado exitosamente");
        } catch (Exception $e) {
            Log::error('Error al actualizar pedido', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Eliminar pedido
     */
    public function destroy(Pedido $pedido, RegisterStockMovementAction $registerStockMovement)
    {
        DB::beginTransaction();
        try {
            $pedido = Pedido::query()
                ->with('productos')
                ->lockForUpdate()
                ->findOrFail($pedido->getKey());

            if ($pedido->estado === 'completado') {
                DB::rollBack();

                return redirect()->back()
                    ->with('error', '❌ No se puede eliminar un pedido completado');
            }

            $numero_pedido = $pedido->numero_pedido;
            $user = auth()->user();

            // Restaurar stock si no estaba cancelado
            if ($pedido->estado !== 'cancelado') {
                $this->restoreStockForDeletedPedido(
                    $pedido,
                    $registerStockMovement,
                    $user instanceof User ? $user : null,
                );
            }

            $pedido->delete();

            DB::commit();

            Log::warning('Pedido eliminado', [
                'numero_pedido' => $numero_pedido,
                'usuario' => auth()->id() ?? 'Sistema'
            ]);

            return redirect()->route('admin.pedidos.index')
                ->with('success', "🗑️ Pedido {$numero_pedido} eliminado exitosamente");
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar pedido', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al eliminar el pedido');
        }
    }

    private function restoreStockForDeletedPedido(
        Pedido $pedido,
        RegisterStockMovementAction $registerStockMovement,
        ?User $user,
    ): void
    {
        foreach ($pedido->productos as $producto) {
            $lockedProducto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->getKey());

            $cantidad = (int) $producto->pivot->cantidad;
            $stockAnterior = (int) $lockedProducto->stock;
            $stockNuevo = $stockAnterior + $cantidad;

            $lockedProducto->update([
                'stock' => $stockNuevo,
            ]);

            $registerStockMovement->handle(
                producto: $lockedProducto,
                tipo: StockMovimiento::TIPO_CANCELACION,
                cantidad: $cantidad,
                stockAnterior: $stockAnterior,
                stockNuevo: $stockNuevo,
                pedido: $pedido,
                user: $user,
                motivo: 'Pedido deletion stock restoration',
            );
        }
    }

    /**
     * Cambiar estado del pedido (AJAX)
     */
    public function cambiarEstado(Request $request, Pedido $pedido, UpdatePedidoAction $updatePedidoAction)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,procesando,completado,cancelado'
        ]);
        try {
            $updatePedidoAction->handle([
                'estado' => $request->estado,
            ], $pedido, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Estado cambiado a: ' . ucfirst($request->estado),
                'nuevo_estado' => $request->estado
            ]);
        } catch (Exception $e) {
            $status = str_contains($e->getMessage(), 'Stock insuficiente') ? 400 : 500;

            return response()->json([
                'success' => false,
                'message' => $status === 400 ? $e->getMessage() : 'Error al cambiar estado'
            ], $status);
        }
    }

    /**
     * Imprimir pedido
     */
    public function imprimir(Pedido $pedido)
    {
        $pedido->load(['cliente', 'empleado', 'productos']);
        return view('pedidos.ticket', compact('pedido'));
    }

    /**
     * Exportar pedidos
     */
    public function exportar(Request $request)
    {
        // Load canonical employee fields.
        $query = Pedido::with(['cliente:id,nombre,apellido', 'empleado:id,nombre,rol_operativo']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $pedidos = $query->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        $filename = 'pedidos_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pedidos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Número', 'Cliente', 'Empleado', 'Rol', 'Fecha', 'Total', 'Estado']);

            foreach ($pedidos as $pedido) {
                fputcsv($file, [
                    $pedido->numero_pedido,
                    $pedido->cliente->nombre ?? 'Sin cliente',
                    $pedido->empleado->nombre ?? 'Sin empleado',
                    $pedido->empleado->rol_operativo ?? 'N/A',
                    $pedido->fecha?->format('d/m/Y') . ' ' . substr((string) $pedido->hora, 0, 5),
                    number_format($pedido->total, 2),
                    ucfirst($pedido->estado)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Duplicar pedido
     */
    public function duplicar(Pedido $pedido, RegisterStockMovementAction $registerStockMovement)
    {
        try {
            $nuevoPedido = $pedido->duplicarConProductos($registerStockMovement, auth()->user());

            return redirect()->route('admin.pedidos.show', $nuevoPedido)
                ->with('success', "✅ Pedido duplicado: {$nuevoPedido->numero_pedido}");
        } catch (Exception $e) {
            return redirect()->back()->with('error', '❌ ' . $e->getMessage());
        }
    }
}
