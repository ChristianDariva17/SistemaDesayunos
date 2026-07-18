<?php

namespace App\Http\Controllers\Trabajador;

use App\Actions\Pedido\CreatePedidoAction;
use App\Actions\Pedido\DeletePedidoAction;
use App\Actions\Pedido\DuplicatePedidoAction;
use App\Enums\ProductoEstado;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pedido\StorePedidoRequest;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Queries\PedidoQuery;
use App\Services\PedidoStatsService;
use DomainException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    /**
     * Mostrar listado de pedidos con filtros y estadísticas
     */
    public function index(Request $request, PedidoQuery $pedidoQuery, PedidoStatsService $pedidoStats)
    {
        Gate::authorize('viewAny', Pedido::class);

        $estadisticas = $pedidoStats->get();
        $pedidos = $pedidoQuery->paginate($request);

        $empleados = Empleado::where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'rol_operativo']);

        return view('trabajador.pedidos.index', compact('pedidos', 'estadisticas', 'empleados'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Pedido::class);

        $productos = Producto::where('estado', ProductoEstado::Active->value)
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

        return view('trabajador.pedidos.create', compact('productos', 'empleados', 'clientes', 'cliente_seleccionado'));
    }

    /**
     * Guardar nuevo pedido
     */
    public function store(StorePedidoRequest $request, CreatePedidoAction $createPedidoAction)
    {
        $validated = $request->validated();

        try {
            $pedido = $createPedidoAction->handle($validated, auth()->id());

            return redirect()->route('trabajador.pedidos.show', $pedido)
                ->with('success', "✅ Pedido {$pedido->numero_pedido} creado correctamente");
        } catch (Exception $e) {
            Log::error('Error al crear pedido', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error: '.$e->getMessage());
        }
    }

    /**
     * Mostrar detalles del pedido
     */
    public function show(Pedido $pedido)
    {
        Gate::authorize('view', $pedido);

        $pedido->loadDetails();

        return view('trabajador.pedidos.show', compact('pedido'));
    }

    /**
     * Eliminar pedido
     */
    public function destroy(Pedido $pedido, DeletePedidoAction $deletePedidoAction)
    {
        Gate::authorize('delete', $pedido);

        try {
            $user = auth()->user();
            $numero_pedido = $deletePedidoAction->handle(
                $pedido,
                $user instanceof User ? $user : null,
            );

            Log::warning('Pedido eliminado', [
                'numero_pedido' => $numero_pedido,
                'usuario' => auth()->id() ?? 'Sistema',
            ]);

            return redirect()->route('trabajador.pedidos.index')
                ->with('success', "🗑️ Pedido {$numero_pedido} eliminado exitosamente");
        } catch (DomainException $e) {
            return redirect()->back()
                ->with('error', '❌ '.$e->getMessage());
        } catch (Exception $e) {
            Log::error('Error al eliminar pedido', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al eliminar el pedido');
        }
    }

    /**
     * Imprimir pedido
     */
    public function imprimir(Pedido $pedido)
    {
        Gate::authorize('view', $pedido);

        $pedido->loadDetails();

        return view('pedidos.ticket', compact('pedido'));
    }

    /**
     * Exportar pedidos
     */
    public function exportar(Request $request)
    {
        Gate::authorize('export', Pedido::class);

        // Load canonical employee fields.
        $query = Pedido::with(['cliente:id,nombre,apellido', 'empleado:id,nombre,rol_operativo']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $pedidos = $query->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        $filename = 'pedidos_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pedidos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Número', 'Cliente', 'Empleado', 'Rol', 'Fecha', 'Total', 'Estado']);

            foreach ($pedidos as $pedido) {
                fputcsv($file, [
                    $pedido->numero_pedido,
                    $pedido->cliente->nombre ?? 'Sin cliente',
                    $pedido->empleado->nombre ?? 'Sin empleado',
                    $pedido->empleado->rol_operativo ?? 'N/A',
                    $pedido->fecha?->format('d/m/Y').' '.substr((string) $pedido->hora, 0, 5),
                    number_format($pedido->total, 2),
                    ucfirst($pedido->estado),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Duplicar pedido
     */
    public function duplicar(Pedido $pedido, DuplicatePedidoAction $duplicatePedidoAction)
    {
        Gate::authorize('duplicate', $pedido);

        try {
            $user = auth()->user();
            $nuevoPedido = $duplicatePedidoAction->handle(
                $pedido,
                $user instanceof User ? $user : null,
            );

            return redirect()->route('trabajador.pedidos.show', $nuevoPedido)
                ->with('success', "✅ Pedido duplicado: {$nuevoPedido->numero_pedido}");
        } catch (Exception $e) {
            return redirect()->back()->with('error', '❌ '.$e->getMessage());
        }
    }
}
