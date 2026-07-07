<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClienteRequest;
use App\Http\Requests\Admin\UpdateClienteRequest;
use App\Models\Cliente;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    /**
     * ==========================================
     * MÉTODO: INDEX - LISTADO DE CLIENTES
     * ==========================================
     * Muestra todos los clientes con filtros, búsqueda y estadísticas
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cliente::class);

        $query = Cliente::query();

        // ==========================================
        // ESTADÍSTICAS DEL DASHBOARD
        // ==========================================
        $totalClientes = Cliente::count();
        $clientesActivos = Cliente::where('estado', 'activo')->count();
        $clientesInactivos = Cliente::where('estado', 'inactivo')->count();
        $nuevosEsteMes = Cliente::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ==========================================
        // FILTRO: BÚSQUEDA GENERAL
        // ==========================================
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        // ==========================================
        // FILTRO: POR ESTADO
        // ==========================================
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // ==========================================
        // ORDENAMIENTO DINÁMICO
        // ==========================================
        switch ($request->get('sort', 'nombre_asc')) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'reciente':
                $query->orderBy('created_at', 'desc');
                break;
            case 'antiguo':
                $query->orderBy('created_at', 'asc');
                break;
            case 'pedidos_desc':
                $query->withCount('pedidos')->orderBy('pedidos_count', 'desc');
                break;
        }

        // ==========================================
        // PAGINACIÓN CONFIGURABLE
        // ==========================================
        $perPage = $request->get('per_page', 10);
        $clientes = $query->withCount('pedidos')->paginate($perPage)->withQueryString();

        return view('admin.clientes.index', compact(
            'clientes',
            'totalClientes',
            'clientesActivos',
            'clientesInactivos',
            'nuevosEsteMes'
        ));
    }

    /**
     * ==========================================
     * MÉTODO: CREATE - FORMULARIO DE CREACIÓN
     * ==========================================
     */
    public function create()
    {
        Gate::authorize('create', Cliente::class);

        return view('admin.clientes.create');
    }

    /**
     * ==========================================
     * MÉTODO: STORE - GUARDAR NUEVO CLIENTE
     * ==========================================
     */
    public function store(StoreClienteRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            // Crear cliente
            $cliente = Cliente::create($validated);

            DB::commit();

            // Log de auditoría
            Log::info('Cliente creado', [
                'cliente_id' => $cliente->id,
                'nombre_completo' => $this->formatClienteNombre($cliente),
                'email' => $cliente->email,
                'usuario' => auth()->id() ?? 'Sistema',
            ]);

            return redirect()->route('admin.clientes.index')
                ->with('success', '✅ Cliente '.$this->formatClienteNombre($cliente).' creado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error al crear cliente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al crear el cliente. Por favor, intenta nuevamente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: SHOW - VER DETALLE DEL CLIENTE
     * ==========================================
     */
    public function show(Cliente $cliente)
    {
        Gate::authorize('view', $cliente);

        // Cargar pedidos recientes con conteo total
        $cliente->loadCount('pedidos')
            ->load(['pedidos' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->with('productos')
                    ->take(5);
            }]);

        // ==========================================
        // CALCULAR ESTADÍSTICAS DEL CLIENTE
        // ==========================================
        $totalGastado = $cliente->pedidos()
            ->where('estado', 'completado')
            ->sum('total');

        $ultimoPedido = $cliente->pedidos()
            ->orderBy('created_at', 'desc')
            ->first();

        // Calcular edad si tiene fecha de nacimiento
        $edad = null;
        if ($cliente->fecha_nacimiento) {
            $edad = now()->diffInYears($cliente->fecha_nacimiento);
        }

        return view('admin.clientes.show', compact(
            'cliente',
            'totalGastado',
            'ultimoPedido',
            'edad'
        ));
    }

    /**
     * ==========================================
     * MÉTODO: EDIT - FORMULARIO DE EDICIÓN
     * ==========================================
     */
    public function edit(Cliente $cliente)
    {
        Gate::authorize('update', $cliente);

        return view('admin.clientes.edit', compact('cliente'));
    }

    /**
     * ==========================================
     * MÉTODO: UPDATE - ACTUALIZAR CLIENTE
     * ==========================================
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            // Actualizar cliente
            $cliente->update($validated);

            DB::commit();

            // Log de auditoría
            Log::info('Cliente actualizado', [
                'cliente_id' => $cliente->id,
                'nombre_completo' => $this->formatClienteNombre($cliente),
                'cambios' => $cliente->getChanges(),
            ]);

            return redirect()->route('admin.clientes.show', $cliente)
                ->with('success', '✅ Cliente '.$this->formatClienteNombre($cliente).' actualizado correctamente');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar cliente', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al actualizar el cliente. Por favor, intenta nuevamente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: DESTROY - ELIMINAR CLIENTE
     * ==========================================
     */
    public function destroy(Cliente $cliente)
    {
        Gate::authorize('delete', $cliente);

        try {
            $nombreCompleto = $this->formatClienteNombre($cliente);
            $clienteId = $cliente->id;

            // ==========================================
            // VALIDACIÓN: Verificar si tiene pedidos
            // ==========================================
            if ($cliente->pedidos()->exists()) {
                $cantidadPedidos = $cliente->pedidos()->count();

                return redirect()->back()
                    ->with('error', "❌ No se puede eliminar '{$nombreCompleto}' porque tiene {$cantidadPedidos} pedido(s) asociado(s).");
            }

            DB::beginTransaction();

            // Eliminar cliente
            $cliente->delete();

            DB::commit();

            // Log de auditoría
            Log::warning('Cliente eliminado', [
                'cliente_id' => $clienteId,
                'nombre_completo' => $nombreCompleto,
                'email' => $cliente->email,
                'usuario' => auth()->id() ?? 'Sistema',
            ]);

            return redirect()->route('admin.clientes.index')
                ->with('success', "🗑️ Cliente '{$nombreCompleto}' eliminado correctamente");

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar cliente', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al eliminar el cliente. Por favor, intenta nuevamente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: BUSCAR - BÚSQUEDA AJAX
     * ==========================================
     * Ruta: GET /clientes/buscar?q=termino
     */
    public function buscar(Request $request)
    {
        Gate::authorize('viewAny', Cliente::class);

        try {
            $termino = $request->get('q', '');

            $clientes = Cliente::where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('apellido', 'like', "%{$termino}%")
                    ->orWhere('email', 'like', "%{$termino}%")
                    ->orWhere('telefono', 'like', "%{$termino}%");
            })
                ->where('estado', 'activo')
                ->limit(10)
                ->get(['id', 'nombre', 'apellido', 'email', 'telefono']);

            return response()->json([
                'success' => true,
                'clientes' => $clientes->map(function ($cliente) {
                    return [
                        'id' => $cliente->id,
                        'nombre_completo' => $this->formatClienteNombre($cliente),
                        'email' => $cliente->email,
                        'telefono' => $cliente->telefono,
                    ];
                }),
            ]);

        } catch (Exception $e) {
            Log::error('Error en búsqueda de clientes', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes',
            ], 500);
        }
    }

    /**
     * ==========================================
     * MÉTODO: TOGGLE ESTADO - ACTIVAR/DESACTIVAR
     * ==========================================
     * Ruta: PATCH /clientes/{cliente}/toggle-estado
     */
    public function toggleEstado(Cliente $cliente)
    {
        Gate::authorize('toggleStatus', $cliente);

        try {
            $estadoAnterior = $cliente->estado;
            $nuevoEstado = $cliente->estado === 'activo' ? 'inactivo' : 'activo';

            $cliente->update(['estado' => $nuevoEstado]);

            // ✅ MEJORADO: Logging de cambio de estado
            Log::info('Estado de cliente cambiado', [
                'cliente_id' => $cliente->id,
                'nombre_completo' => $this->formatClienteNombre($cliente),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'usuario' => auth()->id() ?? 'Sistema',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado cambiado a: '.ucfirst($nuevoEstado),
                'nuevo_estado' => $nuevoEstado,
            ]);

        } catch (Exception $e) {
            Log::error('Error al cambiar estado de cliente', [
                'cliente_id' => $cliente->id,
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
     * MÉTODO: EXPORTAR - EXPORTAR CLIENTES A CSV
     * ==========================================
     * Ruta: GET /clientes/exportar
     * ✅ MEJORADO: Incluye todos los campos de la tabla
     */
    public function exportar(Request $request)
    {
        Gate::authorize('export', Cliente::class);

        try {
            $query = Cliente::query();

            // Aplicar filtro de búsqueda si existe
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Aplicar filtro de estado si existe
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            $clientes = $query->orderBy('nombre')->get();

            // Nombre del archivo
            $filename = 'clientes_'.now()->format('Y-m-d_His').'.csv';

            // Headers para descarga
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            // Crear CSV
            $callback = function () use ($clientes) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // ✅ MEJORADO: Encabezados completos según tabla
                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Apellido',
                    'Teléfono',
                    'Email',
                    'Dirección',
                    'Fecha Nacimiento',
                    'Edad',
                    'Estado',
                    'Notas',
                    'Fecha Registro',
                ]);

                // Datos
                foreach ($clientes as $cliente) {
                    // Calcular edad si tiene fecha de nacimiento
                    $edad = '';
                    if ($cliente->fecha_nacimiento) {
                        $edad = now()->diffInYears($cliente->fecha_nacimiento).' años';
                    }

                    fputcsv($file, [
                        $cliente->id,
                        $cliente->nombre,
                        $cliente->apellido,
                        $cliente->telefono,
                        $cliente->email,
                        $cliente->direccion,
                        $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('d/m/Y') : '',
                        $edad,
                        ucfirst($cliente->estado),
                        $cliente->notas,
                        $cliente->created_at->format('d/m/Y H:i:s'),
                    ]);
                }

                fclose($file);
            };

            Log::info('Clientes exportados', [
                'total' => $clientes->count(),
                'usuario' => auth()->id() ?? 'Sistema',
            ]);

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('Error al exportar clientes', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al exportar los clientes.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: DUPLICAR - DUPLICAR CLIENTE
     * ==========================================
     * Ruta: POST /clientes/{cliente}/duplicar
     * ✅ NUEVO: Crea copia del cliente para casos similares
     */
    public function duplicar(Cliente $cliente)
    {
        Gate::authorize('duplicate', $cliente);

        try {
            DB::beginTransaction();

            // Crear copia del cliente
            $nuevoCliente = $cliente->replicate();
            $nuevoCliente->nombre = $cliente->nombre;
            $nuevoCliente->apellido = $cliente->apellido ? $cliente->apellido.' (Copia)' : null;
            $nuevoCliente->email = null; // Limpiar email único
            $nuevoCliente->telefono = null; // Limpiar teléfono

            $nuevoCliente->save();

            DB::commit();

            Log::info('Cliente duplicado', [
                'cliente_original' => $cliente->id,
                'cliente_nuevo' => $nuevoCliente->id,
                'usuario' => auth()->id() ?? 'Sistema',
            ]);

            return redirect()->route('admin.clientes.edit', $nuevoCliente)
                ->with('success', '✅ Cliente duplicado correctamente. Completa los datos únicos (email, teléfono).');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error al duplicar cliente', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '❌ Error al duplicar el cliente.');
        }
    }

    /**
     * ==========================================
     * MÉTODO: ESTADÍSTICAS - DASHBOARD JSON
     * ==========================================
     * Ruta: GET /clientes/estadisticas
     * ✅ NUEVO: Retorna estadísticas detalladas para gráficos
     */
    public function estadisticas()
    {
        Gate::authorize('viewAny', Cliente::class);

        try {
            $estadisticas = [
                'total' => Cliente::count(),
                'activos' => Cliente::where('estado', 'activo')->count(),
                'inactivos' => Cliente::where('estado', 'inactivo')->count(),
                'nuevos_este_mes' => Cliente::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'nuevos_este_año' => Cliente::whereYear('created_at', now()->year)->count(),
                'con_pedidos' => Cliente::has('pedidos')->count(),
                'sin_pedidos' => Cliente::doesntHave('pedidos')->count(),
                'edad_promedio' => Cliente::whereNotNull('fecha_nacimiento')
                    ->get()
                    ->avg(function ($cliente) {
                        return now()->diffInYears($cliente->fecha_nacimiento);
                    }),
                'registros_por_mes' => Cliente::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                    ->whereYear('created_at', now()->year)
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas,
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas de clientes', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
            ], 500);
        }
    }

    private function formatClienteNombre(Cliente $cliente): string
    {
        return trim($cliente->nombre.' '.($cliente->apellido ?? ''));
    }
}
