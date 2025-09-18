@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('pedidos.index') }}" 
                   class="p-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:scale-105 transition-all duration-200 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $pedido->numero_pedido }}
                </h1>
                @php
                    $statusConfig = [
                        'pendiente' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'icon' => 'fa-clock'],
                        'procesando' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'icon' => 'fa-cogs'],
                        'completado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'icon' => 'fa-check-circle'],
                        'cancelado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'fa-times-circle']
                    ];
                    $config = $statusConfig[$pedido->estado] ?? $statusConfig['pendiente'];
                @endphp
                <span class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full border {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                    <i class="fas {{ $config['icon'] }} mr-2"></i>
                    {{ ucfirst($pedido->estado) }}
                </span>
            </div>
            <p class="text-gray-600 flex items-center">
                <i class="fas fa-utensils mr-2 text-orange-500"></i>
                Detalles completos del pedido de desayunos
            </p>
        </div>
        
        <div class="flex space-x-3">
            <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-print mr-2"></i>
                Imprimir Ticket
            </button>
            <a href="{{ route('pedidos.edit', $pedido) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                <i class="fas fa-edit mr-2"></i>
                Editar Pedido
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-red-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-orange-600"></i>
                        Información del Cliente
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 bg-gradient-to-r from-orange-400 to-red-500 rounded-full flex items-center justify-center shadow-lg">
                            <span class="text-white text-2xl font-bold">{{ substr($pedido->cliente->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xl font-bold text-gray-900">{{ $pedido->cliente->name }}</h4>
                            <div class="flex items-center mt-1 text-gray-600">
                                <i class="fas fa-envelope mr-2 text-orange-500"></i>
                                <span>{{ $pedido->cliente->email }}</span>
                            </div>
                            @if($pedido->cliente->phone)
                                <div class="flex items-center mt-1 text-gray-600">
                                    <i class="fas fa-phone mr-2 text-orange-500"></i>
                                    <span>{{ $pedido->cliente->phone }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                <i class="fas fa-star mr-1"></i>
                                Cliente VIP
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-shopping-bag mr-2 text-green-600"></i>
                            Productos del Pedido
                        </h3>
                        <span class="text-sm text-green-600 font-medium">
                            {{ $pedido->detalles->count() }} productos
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Producto
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Cantidad
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Precio Unit.
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Subtotal
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pedido->detalles as $detalle)
                                <tr class="hover:bg-gradient-to-r hover:from-orange-50/30 hover:to-red-50/30 transition-all duration-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 bg-gradient-to-r from-orange-100 to-red-100 rounded-lg flex items-center justify-center mr-4 shadow-sm">
                                                <i class="fas fa-utensils text-orange-600"></i>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $detalle->producto->nombre }}</div>
                                                @if($detalle->producto->descripcion)
                                                    <div class="text-sm text-gray-500 mt-1">{{ Str::limit($detalle->producto->descripcion, 60) }}</div>
                                                @endif
                                                <div class="flex items-center mt-1">
                                                    <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">
                                                        Categoría: {{ $detalle->producto->categoria ?? 'General' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold rounded-full shadow-md">
                                            {{ $detalle->cantidad }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-lg font-semibold text-gray-900">
                                            ${{ number_format($detalle->precio_unitario, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                                            ${{ number_format($detalle->subtotal, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Observations -->
            @if($pedido->observaciones)
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-sticky-note mr-2 text-amber-600"></i>
                            Observaciones del Pedido
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-lg p-4">
                            <p class="text-gray-700 leading-relaxed">{{ $pedido->observaciones }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden sticky top-4">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-receipt mr-2 text-purple-600"></i>
                        Resumen del Pedido
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-calculator mr-2 text-gray-400"></i>
                            Subtotal:
                        </span>
                        <span class="text-lg font-semibold text-gray-900">${{ number_format($pedido->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-percentage mr-2 text-gray-400"></i>
                            Impuesto (16%):
                        </span>
                        <span class="text-lg font-semibold text-gray-900">${{ number_format($pedido->impuesto, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl px-4 border-2 border-green-200">
                        <span class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>
                            Total:
                        </span>
                        <span class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                            ${{ number_format($pedido->total, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-gray-600"></i>
                        Detalles del Pedido
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-user-tie mr-2 text-blue-500"></i>
                            Empleado:
                        </span>
                        <span class="font-semibold text-gray-900">{{ $pedido->empleado->name }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-orange-500"></i>
                            Fecha:
                        </span>
                        <span class="font-semibold text-gray-900">{{ $pedido->fecha->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-clock mr-2 text-purple-500"></i>
                            Hora:
                        </span>
                        <span class="font-semibold text-gray-900">{{ $pedido->hora->format('H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-history mr-2 text-green-500"></i>
                            Creado:
                        </span>
                        <span class="font-semibold text-gray-900">{{ $pedido->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-lightning-bolt mr-2 text-indigo-600"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <button class="w-full text-left inline-flex items-center px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fas fa-print mr-3"></i>
                        Imprimir Ticket de Venta
                    </button>
                    <button class="w-full text-left inline-flex items-center px-4 py-3 bg-gradient-to-r from-blue-100 to-cyan-100 hover:from-blue-200 hover:to-cyan-200 text-blue-700 font-medium rounded-lg border border-blue-200 hover:border-blue-300 transition-all duration-200">
                        <i class="fas fa-envelope mr-3"></i>
                        Enviar por Email
                    </button>
                    <button class="w-full text-left inline-flex items-center px-4 py-3 bg-gradient-to-r from-purple-100 to-pink-100 hover:from-purple-200 hover:to-pink-200 text-purple-700 font-medium rounded-lg border border-purple-200 hover:border-purple-300 transition-all duration-200">
                        <i class="fas fa-copy mr-3"></i>
                        Duplicar Pedido
                    </button>
                    <button class="w-full text-left inline-flex items-center px-4 py-3 bg-gradient-to-r from-green-100 to-emerald-100 hover:from-green-200 hover:to-emerald-200 text-green-700 font-medium rounded-lg border border-green-200 hover:border-green-300 transition-all duration-200">
                        <i class="fas fa-whatsapp mr-3"></i>
                        Enviar por WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
