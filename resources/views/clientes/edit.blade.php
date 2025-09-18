@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('clientes.show', $cliente) }}" 
                   class="p-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:scale-105 transition-all duration-200 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Editar Cliente</h1>
            </div>
            <p class="text-gray-600 flex items-center">
                <i class="fas fa-edit mr-2 text-orange-500"></i>
                Actualizar información de {{ $cliente->nombre_completo }}
            </p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-orange-50 to-red-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-user-edit mr-2 text-orange-600"></i>
                Actualizar Información
            </h3>
        </div>

        <form action="{{ route('clientes.update', $cliente) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Los mismos campos que en create.blade.php pero con valores del cliente -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-orange-500"></i>
                        Nombre *
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre', $cliente->nombre) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('nombre') ? 'border-red-500' : '' }}"
                           placeholder="Ingresa el nombre del cliente"
                           required>
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Continuar con todos los campos similares a create... -->
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('clientes.show', $cliente) }}" 
                   class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
