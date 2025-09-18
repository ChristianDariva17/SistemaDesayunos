@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('pedidos.index') }}" 
                   class="p-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:scale-105 transition-all duration-200 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-600 via-red-600 to-pink-600 bg-clip-text text-transparent">
                    Crear Nuevo Pedido
                </h1>
            </div>
            <p class="text-gray-600 flex items-center">
                <i class="fas fa-shopping-cart mr-2 text-orange-500"></i>
                Registra una nueva venta de desayunos y caldos
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-red-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clipboard-list mr-2 text-orange-600"></i>
                        Información del Pedido
                    </h3>
                </div>

                <form id="pedido-form" action="{{ route('pedidos.store') }}" method="POST" class="p-6">
                    @csrf
                    
                    <!-- Cliente y Empleado -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="cliente_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user mr-1 text-orange-500"></i>
                                Cliente *
                            </label>
                            <div class="relative">
                                <select id="cliente_id" name="cliente_id" required
                                        class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('cliente_id') ? 'border-red-500' : '' }}">
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nombre }} {{ $cliente->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 pointer-events-none"></i>
                            </div>
                            @error('cliente_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <div class="mt-2">
                                <a href="{{ route('clientes.create') }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                                    <i class="fas fa-plus mr-1"></i>
                                    Agregar nuevo cliente
                                </a>
                            </div>
                        </div>

                        <div>
                            <label for="empleado_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user-tie mr-1 text-orange-500"></i>
                                Empleado *
                            </label>
                            <div class="relative">
                                <select id="empleado_id" name="empleado_id" required
                                        class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white {{ $errors->has('empleado_id') ? 'border-red-500' : '' }}">
                                    <option value="">Seleccione un empleado</option>
                                    @foreach($empleados as $empleado)
                                        <option value="{{ $empleado->id }}" {{ old('empleado_id') == $empleado->id ? 'selected' : '' }}>
                                            {{ $empleado->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 pointer-events-none"></i>
                            </div>
                            @error('empleado_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Productos Section -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-utensils mr-2 text-green-600"></i>
                                Productos del Pedido *
                            </h3>
                            <button type="button" id="add-product-btn" 
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white text-sm font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                <i class="fas fa-plus mr-2"></i>
                                Agregar Producto
                            </button>
                        </div>

                        <div id="products-container" class="space-y-4">
                            <!-- Productos se agregarán aquí dinámicamente -->
                        </div>

                        <div id="empty-products" class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
                            <div class="h-16 w-16 bg-gradient-to-r from-orange-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-shopping-basket text-orange-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay productos agregados</h3>
                            <p class="text-gray-500 mb-4">Haz clic en "Agregar Producto" para comenzar</p>
                        </div>

                        @error('productos')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-8">
                        <label for="observaciones" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sticky-note mr-1 text-orange-500"></i>
                            Observaciones Especiales
                        </label>
                        <textarea id="observaciones" 
                                  name="observaciones" 
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 resize-none bg-white"
                                  placeholder="Instrucciones especiales, alergias, preferencias del cliente...">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('pedidos.index') }}" 
                           class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 font-medium rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                id="submit-btn">
                            <i class="fas fa-save mr-2"></i>
                            Crear Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar - Resumen -->
        <div class="space-y-6">
            <!-- Resumen del Pedido -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden sticky top-4">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-calculator mr-2 text-purple-600"></i>
                        Resumen del Pedido
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Productos:</span>
                            <span class="font-semibold text-gray-900" id="total-items">0</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold text-gray-900" id="subtotal">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Impuesto (16%):</span>
                            <span class="font-semibold text-gray-900" id="tax">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center py-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg px-4 border-2 border-green-200">
                            <span class="text-xl font-bold text-gray-900">Total:</span>
                            <span class="text-2xl font-bold text-green-600" id="total">$0.00</span>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
                        <h4 class="font-semibold text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Información
                        </h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Los precios incluyen preparación</li>
                            <li>• Tiempo de entrega: 15-20 min</li>
                            <li>• Impuesto aplicado automáticamente</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Productos Populares -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-fire mr-2 text-green-600"></i>
                        Productos Populares
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($productos->take(3) as $producto)
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-slate-50 rounded-lg hover:from-orange-50 hover:to-red-50 transition-colors duration-200 cursor-pointer"
                                 onclick="addQuickProduct({{ $producto->id }}, '{{ $producto->nombre }}', {{ $producto->precio }})">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center">
                                        <i class="fas fa-utensils text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $producto->nombre }}</p>
                                        <p class="text-sm text-orange-600 font-semibold">${{ number_format($producto->precio, 2) }}</p>
                                    </div>
                                </div>
                                <button type="button" class="text-orange-500 hover:text-orange-700 transition-colors duration-200">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template para productos -->
<template id="product-template">
    <div class="product-item bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200">
        <div class="grid grid-cols-12 gap-4 items-center">
            <div class="col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                <select name="productos[][id]" class="product-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200" required>
                    <option value="">Seleccionar producto...</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}" data-price="{{ $producto->precio }}">
                            {{ $producto->nombre }} - ${{ number_format($producto->precio, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
                <input type="number" 
                       name="productos[][cantidad]" 
                       value="1" 
                       min="1" 
                       class="quantity-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200" 
                       required>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Precio</label>
                <div class="price-display text-lg font-semibold text-green-600">$0.00</div>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                <div class="subtotal-display text-lg font-bold text-gray-900">$0.00</div>
            </div>
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                <button type="button" 
                        class="remove-product w-full p-2 text-red-600 bg-red-100 hover:bg-red-200 hover:text-red-700 rounded-lg transition-colors duration-200"
                        title="Eliminar producto">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productCount = 0;
    const productsContainer = document.getElementById('products-container');
    const emptyProducts = document.getElementById('empty-products');
    const addProductBtn = document.getElementById('add-product-btn');
    const submitBtn = document.getElementById('submit-btn');

    // Agregar primer producto automáticamente
    addProduct();

    addProductBtn.addEventListener('click', addProduct);

    function addProduct(productId = null, productName = null, productPrice = null) {
        const template = document.getElementById('product-template');
        const productItem = template.content.cloneNode(true);
        
        // Si se pasan parámetros, preseleccionar el producto
        if (productId && productName && productPrice) {
            const select = productItem.querySelector('.product-select');
            select.value = productId;
            const priceDisplay = productItem.querySelector('.price-display');
            const subtotalDisplay = productItem.querySelector('.subtotal-display');
            priceDisplay.textContent = `$${parseFloat(productPrice).toFixed(2)}`;
            subtotalDisplay.textContent = `$${parseFloat(productPrice).toFixed(2)}`;
        }

        const productElement = productItem.querySelector('.product-item');
        productElement.dataset.index = productCount++;

        productsContainer.appendChild(productItem);
        emptyProducts.style.display = 'none';
        
        attachProductListeners(productElement);
        updateTotals();
    }

    function attachProductListeners(productElement) {
        const productSelect = productElement.querySelector('.product-select');
        const quantityInput = productElement.querySelector('.quantity-input');
        const removeButton = productElement.querySelector('.remove-product');
        const priceDisplay = productElement.querySelector('.price-display');
        const subtotalDisplay = productElement.querySelector('.subtotal-display');

        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.dataset.price || 0;
            priceDisplay.textContent = `$${parseFloat(price).toFixed(2)}`;
            updateProductSubtotal(productElement);
        });

        quantityInput.addEventListener('input', function() {
            updateProductSubtotal(productElement);
        });

        removeButton.addEventListener('click', function() {
            productElement.remove();
            checkEmptyProducts();
            updateTotals();
        });
    }

    function updateProductSubtotal(productElement) {
        const productSelect = productElement.querySelector('.product-select');
        const quantityInput = productElement.querySelector('.quantity-input');
        const subtotalDisplay = productElement.querySelector('.subtotal-display');
        
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = parseFloat(selectedOption.dataset.price) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const subtotal = price * quantity;
        
        subtotalDisplay.textContent = `$${subtotal.toFixed(2)}`;
        updateTotals();
    }

    function updateTotals() {
        const productItems = document.querySelectorAll('.product-item');
        let totalItems = 0;
        let subtotal = 0;

        productItems.forEach(item => {
            const quantityInput = item.querySelector('.quantity-input');
            const productSelect = item.querySelector('.product-select');
            
            if (productSelect.value) {
                const quantity = parseInt(quantityInput.value) || 0;
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.dataset.price) || 0;
                
                totalItems += quantity;
                subtotal += price * quantity;
            }
        });

        const tax = subtotal * 0.16;
        const total = subtotal + tax;

        document.getElementById('total-items').textContent = totalItems;
        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `$${total.toFixed(2)}`;

        // Deshabilitar botón si no hay productos
        submitBtn.disabled = totalItems === 0;
    }

    function checkEmptyProducts() {
        const productItems = document.querySelectorAll('.product-item');
        emptyProducts.style.display = productItems.length === 0 ? 'block' : 'none';
    }

    // Función global para productos populares
    window.addQuickProduct = function(productId, productName, productPrice) {
        addProduct(productId, productName, productPrice);
    };

    // Validación del formulario
    document.getElementById('pedido-form').addEventListener('submit', function(e) {
        const productItems = document.querySelectorAll('.product-item');
        let hasValidProducts = false;

        productItems.forEach(item => {
            const productSelect = item.querySelector('.product-select');
            const quantityInput = item.querySelector('.quantity-input');
            
            if (productSelect.value && parseInt(quantityInput.value) > 0) {
                hasValidProducts = true;
            }
        });

        if (!hasValidProducts) {
            e.preventDefault();
            alert('Debe agregar al menos un producto al pedido.');
            return false;
        }
    });
});
</script>
@endsection
