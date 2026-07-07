<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmpleadoRequest;
use App\Http\Requests\Admin\UpdateEmpleadoRequest;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Empleado::class);

        $query = Empleado::query();

        // Filtro por búsqueda (nombre y rol_operativo)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('rol_operativo', 'like', "%{$search}%");
            });
        }

        // Filtro por rol específico
        if ($request->filled('rol_operativo')) {
            $query->where('rol_operativo', $request->rol_operativo);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento dinámico
        $sortBy = $request->get('sort', 'nombre');
        $sortDirection = $request->get('direction', 'asc');

        // Validar columnas permitidas para ordenar
        $allowedSorts = ['nombre', 'rol_operativo', 'estado', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('nombre', 'asc');
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
        Gate::authorize('create', Empleado::class);

        return view('admin.empleados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmpleadoRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $empleado = Empleado::create($validated);

            DB::commit();

            Log::info("Empleado creado: {$empleado->nombre}", ['id' => $empleado->id]);

            return redirect()->route('admin.empleados.index')
                ->with('success', "✅ Empleado {$empleado->nombre} registrado correctamente");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear empleado: '.$e->getMessage());

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
        Gate::authorize('view', $empleado);

        // Cargar relación con conteo
        $empleado->loadCount('pedidos');

        // Cargar últimos pedidos
        $empleado->load('user');
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
        Gate::authorize('update', $empleado);

        return view('admin.empleados.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmpleadoRequest $request, Empleado $empleado)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $empleado->update($validated);

            DB::commit();

            Log::info("Empleado actualizado: {$empleado->nombre}", ['id' => $empleado->id]);

            return redirect()->route('admin.empleados.show', $empleado)
                ->with('success', "✅ Empleado {$empleado->nombre} actualizado correctamente");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar empleado: '.$e->getMessage());

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
        Gate::authorize('delete', $empleado);

        try {
            $nombre = $empleado->nombre;

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
            Log::error('Error al eliminar empleado: '.$e->getMessage());

            return back()
                ->with('error', '❌ Error al eliminar el empleado. Por favor intenta nuevamente.');
        }
    }

    /**
     * Obtener estadísticas de empleados (método adicional)
     */
    public function estadisticas()
    {
        Gate::authorize('viewAny', Empleado::class);

        $stats = [
            'total' => Empleado::count(),
            'activos' => Empleado::where('estado', 'activo')->count(),
            'inactivos' => Empleado::where('estado', 'inactivo')->count(),
            'por_rol' => Empleado::select('rol_operativo', DB::raw('count(*) as total'))
                ->groupBy('rol_operativo')
                ->pluck('total', 'rol_operativo'),
        ];

        return response()->json($stats);
    }
}
