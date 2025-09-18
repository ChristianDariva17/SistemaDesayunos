@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar empleado</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('empleados.update', $empleado) }}" method="POST" 
          class="bg-white shadow-lg rounded-lg p-6 space-y-5">
        @csrf
        @method('PUT')

        <!-- Nombre -->
        <div>
            <label class="block text-gray-700 font-medium mb-1">Nombre</label>
            <input type="text" 
                   name="name" 
                   value="{{ old('name', $empleado->name) }}" 
                   required
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <!-- Rol -->
        <div>
            <label class="block text-gray-700 font-medium mb-1">Rol</label>
            <select name="role" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="mesero" {{ old('role', $empleado->role)=='mesero' ? 'selected' : '' }}>Mesero</option>
                <option value="cajero" {{ old('role', $empleado->role)=='cajero' ? 'selected' : '' }}>Cajero</option>
                <option value="cocinero" {{ old('role', $empleado->role)=='cocinero' ? 'selected' : '' }}>Cocinero</option>
            </select>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('empleados.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
               Cancelar
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow transition">
                Actualizar
            </button>
        </div>
    </form>
</div>
@endsection
