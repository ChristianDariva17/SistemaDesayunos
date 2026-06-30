<?php

namespace App\Http\Controllers\Trabajador;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

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

        return view('trabajador.clientes.index', compact(
            'clientes',
            'totalClientes',
            'clientesActivos',
            'clientesInactivos',
            'nuevosEsteMes'
        ));
    }


    /**
     * ==========================================
     * MÉTODO: BUSCAR - BÚSQUEDA AJAX
     * ==========================================
     * Ruta: GET /clientes/buscar?q=termino
     */
    public function buscar(Request $request)
    {
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
                        'nombre_completo' => trim($cliente->nombre . ' ' . $cliente->apellido),
                        'email' => $cliente->email,
                        'telefono' => $cliente->telefono
                    ];
                })
            ]);

        } catch (Exception $e) {
            Log::error('Error en búsqueda de clientes', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes'
            ], 500);
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
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas de clientes', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * ==========================================
     * MÉTODO PRIVADO: REGLAS DE VALIDACIÓN
     * ==========================================
     * ✅ MEJORADO: Alineado con estructura de tabla
     */
    private function getValidationRules(Cliente $cliente = null): array
    {
        $clienteId = $cliente ? $cliente->id : null;

        return [
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'telefono' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\+\-\(\)\s]+$/' // Permite números, +, -, (, ), espacios
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:clientes,email,' . $clienteId
            ],
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => [
                'nullable',
                'date',
                'before:today',
                'after:' . now()->subYears(120)->format('Y-m-d') // Máximo 120 años
            ],
            'estado' => 'required|in:activo,inactivo',
            'notas' => 'nullable|string|max:1000'
        ];
    }

    /**
     * ==========================================
     * MÉTODO PRIVADO: MENSAJES DE VALIDACIÓN
     * ==========================================
     */
    private function getValidationMessages(): array
    {
        return [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'apellido.max' => 'El apellido no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado por otro cliente.',
            'email.max' => 'El email no puede tener más de 255 caracteres.',
            'telefono.regex' => 'El formato del teléfono no es válido. Solo números, +, -, ( ) y espacios.',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura.',
            'fecha_nacimiento.after' => 'La fecha de nacimiento no puede ser mayor a 120 años.',
            'estado.required' => 'Debes seleccionar el estado del cliente.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'notas.max' => 'Las notas no pueden tener más de 1000 caracteres.'
        ];
    }
}
