@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-xl font-bold mb-6 text-gray-800">Editar Producto</h2>

    {{-- Errores --}}
    @if($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-700">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('productos.update', $producto) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}"
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
        </div>

        <div>
            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
            <textarea name="descripcion" id="descripcion"
                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('descripcion', $producto->descripcion) }}</textarea>
        </div>

        <div>
            <label for="precio" class="block text-sm font-medium text-gray-700">Precio (S/.)</label>
            <input type="number" name="precio" id="precio" step="0.01" value="{{ old('precio', $producto->precio) }}"
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('productos.index') }}"
               class="text-gray-600 hover:underline">⬅️ Volver</a>
            <button type="submit"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                Actualizar
            </button>
        </div>
    </form>
</div>
@endsection
