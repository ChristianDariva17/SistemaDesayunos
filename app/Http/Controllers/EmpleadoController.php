<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /*public function __construct()
    {
        $this->middleware('auth'); // opcional si solo usuarios logueados pueden acceder
    }*/

    public function index(Request $request)
    {
    $search = $request->input('search');
    $role = $request->input('role');

    $empleados = Empleado::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })
        ->when($role, function ($query, $role) {
            return $query->where('role', $role);
        })
        ->orderBy('name')
        ->paginate(10)
        ->withQueryString();

    return view('empleados.index', compact('empleados', 'search', 'role'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('empleados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:mesero,cajero,cocinero',
        ]);

        Empleado::create($data);

        return redirect()->route('empleados.index')->with('success', 'Empleado registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empleado $empleado)
    {
        return view('empleados.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:mesero,cajero,cocinero',
        ]);

        $empleado->update($data);

        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();
        return back()->with('success', 'Empleado eliminado correctamente.');
    }
}
