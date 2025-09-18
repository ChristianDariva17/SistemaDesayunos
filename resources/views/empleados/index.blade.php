@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Gestión de Empleados</h1>
        <a href="{{ route('empleados.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow transition">
           + Registrar empleado
        </a>
    </div>

    <!-- Barra de búsqueda + filtro -->
    <form method="GET" action="{{ route('empleados.index') }}" class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            
            <!-- Input búsqueda -->
            <input type="text" 
                   name="search" 
                   placeholder="Buscar por nombre..." 
                   value="{{ $search }}"
                   class="w-full md:w-64 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">

            <!-- Filtro por rol -->
            <select name="role" 
                    class="w-full md:w-48 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Todos los roles</option>
                <option value="mesero" {{ $role == 'mesero' ? 'selected' : '' }}>Mesero</option>
                <option value="cajero" {{ $role == 'cajero' ? 'selected' : '' }}>Cajero</option>
                <option value="cocinero" {{ $role == 'cocinero' ? 'selected' : '' }}>Cocinero</option>
            </select>

            <button type="submit" 
                    class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow transition">
                Filtrar
            </button>
        </div>
    </form>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Nombre</th>
                    <th class="px-6 py-3 text-left">Rol</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($empleados as $empleado)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">{{ $empleado->id }}</td>
                    <td class="px-6 py-4 font-medium">{{ $empleado->name }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 text-xs rounded-full
                            @if($empleado->role == 'mesero') bg-yellow-100 text-yellow-800
                            @elseif($empleado->role == 'cajero') bg-blue-100 text-blue-800
                            @elseif($empleado->role == 'cocinero') bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($empleado->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('empleados.edit', $empleado) }}" 
                           class="text-indigo-600 hover:text-indigo-900 font-semibold">Editar</a>

                        <form action="{{ route('empleados.destroy', $empleado) }}" 
                              method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('¿Eliminar empleado?')" 
                                    class="text-red-600 hover:text-red-800 font-semibold">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron empleados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $empleados->links('pagination::tailwind') }}
    </div>
</div>
@endsection
