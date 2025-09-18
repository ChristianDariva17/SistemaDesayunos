@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('clientes.index') }}" 
                   class="p-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:scale-105 transition-all duration-200 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Nuevo Cliente</h1>
            </div>
            <p class="text-gray-600 flex items-center">
                <i class="fas fa-user-plus mr-2 text-orange-500"></i>
                Registra un nuevo cliente en el sistema
            </p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-orange-50 to-red-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-user-circle mr-2 text-orange-600"></i>
                Información del Cliente
            </h3>
        </div>

        <form action="{{ route('clientes.store') }}" method="POST" class="p-6">
            @csrf
            
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
                           value="{{ old('nombre') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('nombre') ? 'border-red-500' : '' }}"
                           placeholder="Ingresa el nombre del cliente"
                           required>
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido -->
                <div>
                    <label for="apellido" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-orange-500"></i>
                        Apellido
                    </label>
                    <input type="text" 
                           id="apellido" 
                           name="apellido" 
                           value="{{ old('apellido') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('apellido') ? 'border-red-500' : '' }}"
                           placeholder="Ingresa el apellido del cliente">
                    @error('apellido')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1 text-orange-500"></i>
                        Correo Electrónico *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('email') ? 'border-red-500' : '' }}"
                           placeholder="ejemplo@correo.com"
                           required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone mr-1 text-orange-500"></i>
                        Teléfono
                    </label>
                    <input type="tel" 
                           id="telefono" 
                           name="telefono" 
                           value="{{ old('telefono') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('telefono') ? 'border-red-500' : '' }}"
                           placeholder="+1234567890">
                    @error('telefono')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha de Nacimiento -->
                <div>
                    <label for="fecha_nacimiento" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-birthday-cake mr-1 text-orange-500"></i>
                        Fecha de Nacimiento
                    </label>
                    <input type="date" 
                           id="fecha_nacimiento" 
                           name="fecha_nacimiento" 
                           value="{{ old('fecha_nacimiento') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('fecha_nacimiento') ? 'border-red-500' : '' }}">
                    @error('fecha_nacimiento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-1 text-orange-500"></i>
                        Estado *
                    </label>
                    <select id="estado" 
                            name="estado" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('estado') ? 'border-red-500' : '' }}"
                            required>
                        <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('estado')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dirección (full width) -->
            <div class="mt-6">
                <label for="direccion" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-1 text-orange-500"></i>
                    Dirección Completa
                </label>
                <input type="text" 
                       id="direccion" 
                       name="direccion" 
                       value="{{ old('direccion') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('direccion') ? 'border-red-500' : '' }}"
                       placeholder="Calle, número, colonia, ciudad...">
                @error('direccion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notas (full width) -->
            <div class="mt-6">
                <label for="notas" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-sticky-note mr-1 text-orange-500"></i>
                    Notas Adicionales
                </label>
                <textarea id="notas" 
                          name="notas" 
                          rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 resize-none bg-white {{ $errors->has('notas') ? 'border-red-500' : '' }}"
                          placeholder="Información adicional sobre el cliente...">{{ old('notas') }}</textarea>
                @error('notas')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('clientes.index') }}" 
                   class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
