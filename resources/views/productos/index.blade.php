@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Lista de Productos</h2>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('productos.create') }}"
       class="inline-block px-4 py-2 mb-4 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
       ➕ Nuevo Producto
    </a>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="w-full border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left text-gray-600">ID</th>
                    <th class="p-3 text-left text-gray-600">Nombre</th>
                    <th class="p-3 text-left text-gray-600">Descripción</th>
                    <th class="p-3 text-left text-gray-600">Precio (S/.)</th>
                    <th class="p-3 text-center text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ $producto->id }}</td>
                        <td class="p-3 font-semibold">{{ $producto->nombre }}</td>
                        <td class="p-3">{{ $producto->descripcion }}</td>
                        <td class="p-3">S/ {{ number_format($producto->precio, 2) }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ route('productos.edit', $producto) }}"
                               class="px-3 py-1 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                               ✏️ Editar
                            </a>
                            <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('¿Seguro que deseas eliminar este producto?')"
                                        class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">No hay productos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
