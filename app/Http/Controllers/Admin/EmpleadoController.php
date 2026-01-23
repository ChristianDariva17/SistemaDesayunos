<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Empleado::query();

        // Filtro por búsqueda (name y role)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Filtro por rol específico
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento dinámico
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        // Validar columnas permitidas para ordenar
        $allowedSorts = ['name', 'role', 'estado', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Paginación configurable
        $perPage = $request->get('per_page', 10);
        $empleados = $query->paginate($perPage)->withQueryString();

        return view('admin.empleados.index', compact('empleados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.empleados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateEmpleado($request);

            DB::beginTransaction();

            $empleado = Empleado::create($validated);

            DB::commit();

            Log::info("Empleado creado: {$empleado->name}", ['id' => $empleado->id]);

            return redirect()->route('admin.empleados.index')
                ->with('success', "✅ Empleado {$empleado->name} registrado correctamente");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear empleado: " . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', '❌ Error al registrar el empleado. Por favor intenta nuevamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        // Cargar relación con conteo
        $empleado->loadCount('pedidos');

        // Cargar últimos pedidos
        $empleado->load(['pedidos' => function ($query) {
            $query->latest()->take(5);
        }]);

        return view('admin.empleados.show', compact('empleado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empleado $empleado)
    {
        return view('admin.empleados.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleado $empleado)
    {
        try {
            $validated = $this->validateEmpleado($request, $empleado->id);

            DB::beginTransaction();

            $empleado->update($validated);

            DB::commit();

            Log::info("Empleado actualizado: {$empleado->name}", ['id' => $empleado->id]);

            return redirect()->route('admin.empleados.show', $empleado)
                ->with('success', "✅ Empleado {$empleado->name} actualizado correctamente");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar empleado: " . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', '❌ Error al actualizar el empleado. Por favor intenta nuevamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empleado $empleado)
    {
        try {
            $nombre = $empleado->name;

            // ✅ VALIDAR SI TIENE PEDIDOS ANTES DE ELIMINAR
            if ($empleado->pedidos()->count() > 0) {
                return redirect()->route('admin.empleados.index')
                    ->with('error', "❌ No se puede eliminar a {$nombre} porque tiene {$empleado->pedidos()->count()} pedido(s) asignado(s)");
            }

            DB::beginTransaction();

            $empleado->delete();

            DB::commit();

            Log::info("Empleado eliminado: {$nombre}");

            return redirect()->route('admin.empleados.index')
                ->with('success', "✅ Empleado {$nombre} eliminado correctamente");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar empleado: " . $e->getMessage());

            return back()
                ->with('error', '❌ Error al eliminar el empleado. Por favor intenta nuevamente.');
        }
    }

    /**
     * Validar datos del empleado (reutilizable)
     * 
     * @param Request $request
     * @param int|null $empleadoId
     * @return array
     */
    private function validateEmpleado(Request $request, $empleadoId = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:mesero,cajero,cocinero,chef,ayudante',
            'estado' => 'required|in:activo,inactivo',
        ];

        // Si el modelo tiene email, agregar validación única
        // 'email' => 'required|email|unique:empleados,email,' . $empleadoId,

        $messages = [
            'name.required' => 'El nombre del empleado es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'role.required' => 'Debes seleccionar un rol para el empleado.',
            'role.in' => 'El rol seleccionado no es válido.',
            'estado.required' => 'Debes seleccionar el estado del empleado.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];

        return $request->validate($rules, $messages);
    }

    /**
     * Obtener estadísticas de empleados (método adicional)
     */
    public function estadisticas()
    {
        $stats = [
            'total' => Empleado::count(),
            'activos' => Empleado::where('estado', 'activo')->count(),
            'inactivos' => Empleado::where('estado', 'inactivo')->count(),
            'por_rol' => Empleado::select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->pluck('total', 'role'),
        ];

        return response()->json($stats);
    }
}
