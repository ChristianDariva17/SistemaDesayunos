@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('clientes.index') }}" 
                   class="p-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:scale-105 transition-all duration-200 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $cliente->nombre_completo }}</h1>
                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full {{ $cliente->estado == 'activo' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                    <i class="fas {{ $cliente->estado == 'activo' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                    {{ ucfirst($cliente->estado) }}
                </span>
            </div>
            <p class="text-gray-600 flex items-center">
                <i class="fas fa-user-circle mr-2 text-orange-500"></i>
                Cliente desde {{ $cliente->created_at->format('d M, Y') }}
            </p>
        </div>
        
        <div class="flex space-x-3">
            <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-phone mr-2"></i>
                Llamar
            </button>
            <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-edit mr-2"></i>
                Editar Cliente
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Client Info -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-red-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-id-card mr-2 text-orange-600"></i>
                        Información Personal
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                                <i class="fas fa-envelope text-blue-500"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="font-semibold text-gray-900">{{ $cliente->email }}</p>
                                </div>
                            </div>
                            
                            @if($cliente->telefono)
                                <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                                    <i class="fas fa-phone text-green-500"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Teléfono</p>
                                        <p class="font-semibold text-gray-900">{{ $cliente->telefono }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($cliente->fecha_nacimiento)
                                <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                                    <i class="fas fa-birthday-cake text-purple-500"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Fecha de Nacimiento</p>
                                        <p class="font-semibold text-gray-900">{{ $cliente->fecha_nacimiento->format('d M, Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $cliente->fecha_nacimiento->age }} años</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="space-y-4">
                            @if($cliente->direccion)
                                <div class="flex items-start space-x-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg">
                                    <i class="fas fa-map-marker-alt text-amber-500 mt-1"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Dirección</p>
                                        <p class="font-semibold text-gray-900">{{ $cliente->direccion }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-gray-50 to-slate-50 rounded-lg">
                                <i class="fas fa-calendar-plus text-gray-500"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Registrado</p>
                                    <p class="font-semibold text-gray-900">{{ $cliente->created_at->format('d M, Y H:i') }}</p>
                                    <p class="text-xs text-gray-500">{{ $cliente->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($cliente->notas)
                        <div class="mt-6 p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg border border-indigo-200">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-sticky-note mr-2 text-indigo-500"></i>
                                Notas
                            </h4>
                            <p class="text-gray-700 leading-relaxed">{{ $cliente->notas }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Orders -->
            @if($cliente->pedidos->count() > 0)
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-shopping-cart mr-2 text-green-600"></i>
                                Pedidos Recientes
                            </h3>
                            <a href="{{ route('pedidos.index') }}?cliente={{ $cliente->id }}" class="text-sm text-green-600 hover:text-green-700 font-medium">
                                Ver todos →
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pedido</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($cliente->pedidos as $pedido)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 font-medium text-orange-600">{{ $pedido->numero_pedido }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-green-600">${{ number_format($pedido->total, 2) }}</td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusConfig = [
                                                    'pendiente' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                                                    'completado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                                    'cancelado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800']
                                                ];
                                                $config = $statusConfig[$pedido->estado] ?? $statusConfig['pendiente'];
                                            @endphp
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                                {{ ucfirst($pedido->estado) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('pedidos.show', $pedido) }}" 
                                               class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                                Ver detalles
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Stats -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden sticky top-4">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
                        Estadísticas
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="text-center p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                        <p class="text-3xl font-bold text-blue-600">{{ $cliente->pedidos->count() }}</p>
                        <p class="text-sm text-blue-600 font-medium">Total Pedidos</p>
                    </div>
                    
                    <div class="text-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <p class="text-3xl font-bold text-green-600">${{ number_format($cliente->pedidos->sum('total'), 0) }}</p>
                        <p class="text-sm text-green-600 font-medium">Total Gastado</p>
                    </div>
                    
                    @if($cliente->pedidos->count() > 0)
                        <div class="text-center p-4 bg-gradient-to-r from-orange-50 to-red-50 rounded-lg">
                            <p class="text-3xl font-bold text-orange-600">${{ number_format($cliente->pedidos->avg('total'), 0) }}</p>
                            <p class="text-sm text-orange-600 font-medium">Promedio por Pedido</p>
                        </div>
                    @endif
                    
                    <div class="text-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                        <p class="text-3xl font-bold text-purple-600">{{ $cliente->pedidos->where('estado', 'completado')->count() }}</p>
                        <p class="text-sm text-purple-600 font-medium">Pedidos Completados</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-indigo-600"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('pedidos.create') }}?cliente={{ $cliente->id }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Nuevo Pedido
                    </a>
                    
                    <button class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-100 to-cyan-100 hover:from-blue-200 hover:to-cyan-200 text-blue-700 font-medium rounded-lg border border-blue-200 hover:border-blue-300 transition-all duration-200">
                        <i class="fas fa-phone mr-2"></i>
                        Llamar Cliente
                    </button>
                    
                    <button class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-100 to-emerald-100 hover:from-green-200 hover:to-emerald-200 text-green-700 font-medium rounded-lg border border-green-200 hover:border-green-300 transition-all duration-200">
                        <i class="fas fa-envelope mr-2"></i>
                        Enviar Email
                    </button>
                    
                    <button class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-purple-100 to-pink-100 hover:from-purple-200 hover:to-pink-200 text-purple-700 font-medium rounded-lg border border-purple-200 hover:border-purple-300 transition-all duration-200">
                        <i class="fas fa-whatsapp mr-2"></i>
                        WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
