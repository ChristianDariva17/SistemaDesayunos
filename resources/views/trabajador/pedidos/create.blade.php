<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <meta name="description" content="Crear Nuevo Pedido - Sistema Caldos & Desayunos - Trabajador">
    <meta name="keywords" content="pedido, crear, nuevo, trabajador">
    <meta name="author" content="Caldos & Desayunos">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Title --}}
    <title>Nuevo Pedido - Caldos & Desayunos</title>
    
    {{-- ============================================
        ESTILOS CSS
    ============================================= --}}
    
    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- ============================================
        ESTILOS PERSONALIZADOS
    ============================================= --}}
    <style>
        /* ============================================
           VARIABLES CSS
        ============================================= */
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --font-family: 'Poppins', sans-serif;
        }

        /* ============================================
           ESTILOS GENERALES
        ============================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--light-color);
            color: var(--dark-color);
            font-size: 14px;
            line-height: 1.6;
        }

        /* ============================================
           NAVBAR SUPERIOR
        ============================================= */
        .navbar-top {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-user .user-name {
            color: white;
            font-weight: 500;
        }

        .navbar-user .badge-role {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* ============================================
           TARJETAS
        ============================================= */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .border-left-primary {
            border-left: 4px solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 4px solid var(--success-color) !important;
        }

        .border-left-info {
            border-left: 4px solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 4px solid var(--warning-color) !important;
        }

        /* ============================================
           COLORES DE TEXTO
        ============================================= */
        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .text-xs {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ============================================
           ANIMACIONES
        ============================================= */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade {
            animation: fadeIn 0.5s ease;
        }

        /* ============================================
           FORMULARIOS
        ============================================= */
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid #d1d3e2;
            border-radius: 8px;
            padding: 0.625rem 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* ============================================
           TABLA DE PRODUCTOS
        ============================================= */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--light-color);
        }

        .producto-row {
            animation: fadeIn 0.3s ease;
        }

        /* ============================================
           BOTONES
        ============================================= */
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* ============================================
           RESUMEN DE TOTALES
        ============================================= */
        .resumen-totales {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .resumen-totales .total-amount {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>

{{-- ============================================
    NAVBAR SUPERIOR
============================================= --}}
<nav class="navbar navbar-top mb-4">
    <div class="container-fluid">
        <a href="{{ route('trabajador.dashboard') }}" class="navbar-brand mb-0 h1">
            <i class="fas fa-utensils"></i>
            Caldos & Desayunos - Panel Trabajador
        </a>
        <div class="navbar-user">
            <span class="user-name">
                <i class="fas fa-user me-1"></i>
                {{ Auth::user()->name ?? 'Trabajador' }}
            </span>
            <span class="badge-role">
                <i class="fas fa-id-badge me-1"></i>
                Trabajador
            </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ============================================
    CONTENIDO PRINCIPAL
============================================= --}}
<div class="container-fluid py-4">
    
    {{-- ============================================
        ENCABEZADO
    ============================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-plus-circle text-success me-2"></i>
                <strong>Crear Nuevo Pedido</strong>
            </h1>
            <p class="text-muted mb-0">
                <i class="far fa-edit me-1"></i>
                Registra una nueva venta de desayunos y caldos
            </p>
        </div>
        <div>
            <a href="{{ route('trabajador.pedidos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
        </div>
    </div>

    {{-- ============================================
        ERRORES DE VALIDACIÓN
    ============================================= --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 animate-fade" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-2">¡Errores de Validación!</h5>
                    <p class="mb-2">Por favor corrige los siguientes errores:</p>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ============================================
        FORMULARIO DE CREACIÓN
    ============================================= --}}
    <form action="{{ route('trabajador.pedidos.store') }}" method="POST" id="formPedido">
        @csrf

        <div class="row g-4">
            
            {{-- ============================================
                COLUMNA IZQUIERDA - DATOS DEL PEDIDO
            ============================================= --}}
            <div class="col-lg-8">
                
                {{-- INFORMACIÓN BÁSICA --}}
                <div class="card shadow-sm mb-4 border-left-primary animate-fade">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle me-2"></i>Información Básica
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            
                            {{-- CLIENTE --}}
                            <div class="col-md-6">
                                <label for="cliente_id" class="form-label">
                                    <i class="fas fa-user me-1"></i>Cliente <span class="text-danger">*</span>
                                </label>
                                <select name="cliente_id" id="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nombre }} {{ $cliente->apellido }} - {{ $cliente->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- EMPLEADO --}}
                            <div class="col-md-6">
                                <label for="empleado_id" class="form-label">
                                    <i class="fas fa-user-tie me-1"></i>Empleado <span class="text-danger">*</span>
                                </label>
                                <select name="empleado_id" id="empleado_id" class="form-select @error('empleado_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un empleado</option>
                                    @foreach($empleados as $empleado)
                                        <option value="{{ $empleado->id }}" {{ old('empleado_id') == $empleado->id ? 'selected' : '' }}>
                                            {{ $empleado->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('empleado_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- FECHA --}}
                            <div class="col-md-6">
                                <label for="fecha" class="form-label">
                                    <i class="far fa-calendar me-1"></i>Fecha <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha" 
                                       id="fecha" 
                                       class="form-control @error('fecha') is-invalid @enderror" 
                                       value="{{ old('fecha', date('Y-m-d')) }}"
                                       required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- HORA --}}
                            <div class="col-md-6">
                                <label for="hora" class="form-label">
                                    <i class="far fa-clock me-1"></i>Hora <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       name="hora" 
                                       id="hora" 
                                       class="form-control @error('hora') is-invalid @enderror" 
                                       value="{{ old('hora', date('H:i')) }}"
                                       required>
                                @error('hora')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- OBSERVACIONES --}}
                            <div class="col-12">
                                <label for="observaciones" class="form-label">
                                    <i class="fas fa-comment me-1"></i>Observaciones
                                </label>
                                <textarea name="observaciones" 
                                          id="observaciones" 
                                          rows="3" 
                                          class="form-control @error('observaciones') is-invalid @enderror"
                                          placeholder="Ingrese observaciones adicionales (opcional)">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- PRODUCTOS --}}
                <div class="card shadow-sm border-left-success animate-fade">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-shopping-basket me-2"></i>Productos del Pedido
                        </h6>
                        <button type="button" class="btn btn-light btn-sm" id="btnAgregarProducto">
                            <i class="fas fa-plus me-1"></i>Agregar Producto
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="tablaProductos">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Producto</th>
                                        <th width="15%" class="text-center">Cantidad</th>
                                        <th width="20%" class="text-end">Precio Unit.</th>
                                        <th width="20%" class="text-end">Subtotal</th>
                                        <th width="5%" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="productosBody">
                                    <tr id="emptyRow">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            <p class="mb-0">Haz clic en "Agregar Producto" para comenzar</p>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light d-none" id="totalFooter">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                        <td class="text-end">
                                            <h5 class="mb-0 text-success">
                                                <strong>S/ <span id="totalGeneral">0.00</span></strong>
                                            </h5>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================================
                COLUMNA DERECHA - RESUMEN
            ============================================= --}}
            <div class="col-lg-4">
                
                {{-- RESUMEN DE TOTALES --}}
                <div class="resumen-totales mb-4 animate-fade">
                    <div class="text-center">
                        <i class="fas fa-calculator fa-3x mb-3 opacity-75"></i>
                        <h5 class="mb-2">Total del Pedido</h5>
                        <div class="total-amount mb-3">
                            S/ <span id="totalResumen">0.00</span>
                        </div>
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <small class="d-block opacity-75">Productos</small>
                                <strong class="fs-4"><span id="cantidadProductos">0</span></strong>
                            </div>
                            <div>
                                <small class="d-block opacity-75">Unidades</small>
                                <strong class="fs-4"><span id="cantidadUnidades">0</span></strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INSTRUCCIONES --}}
                <div class="card shadow-sm border-left-info animate-fade">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-lightbulb me-2"></i>Instrucciones
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Selecciona el cliente y empleado
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Agrega los productos del pedido
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Verifica las cantidades y precios
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Guarda el pedido al finalizar
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- BOTONES DE ACCIÓN --}}
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>Guardar Pedido
                    </button>
                    <a href="{{ route('trabajador.pedidos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>

            </div>

        </div>

    </form>

</div>

{{-- ============================================
    SCRIPTS JAVASCRIPT
============================================= --}}

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Scripts personalizados --}}
<script>
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let contadorProductos = 0;
    const productos = @json($productos);

    // ============================================
    // AGREGAR PRODUCTO
    // ============================================
    document.getElementById('btnAgregarProducto').addEventListener('click', function() {
        // Ocultar mensaje vacío
        document.getElementById('emptyRow').style.display = 'none';
        document.getElementById('totalFooter').classList.remove('d-none');

        // Crear nueva fila
        const fila = document.createElement('tr');
        fila.classList.add('producto-row');
        fila.id = `producto-${contadorProductos}`;

        fila.innerHTML = `
            <td>
                <select name="productos[${contadorProductos}][producto_id]" 
                        class="form-select form-select-sm producto-select" 
                        data-index="${contadorProductos}" 
                        required>
                    <option value="">Seleccione un producto</option>
                    ${productos.map(p => `
                        <option value="${p.id}" data-precio="${p.precio}">
                            ${p.nombre} - S/ ${parseFloat(p.precio).toFixed(2)}
                        </option>
                    `).join('')}
                </select>
            </td>
            <td>
                <input type="number" 
                       name="productos[${contadorProductos}][cantidad]" 
                       class="form-control form-control-sm text-center cantidad-input" 
                       data-index="${contadorProductos}" 
                       min="1" 
                       value="1" 
                       required>
            </td>
            <td class="text-end">
                <strong class="precio-unitario" data-index="${contadorProductos}">S/ 0.00</strong>
                <input type="hidden" 
                       name="productos[${contadorProductos}][precio_unitario]" 
                       class="precio-hidden" 
                       data-index="${contadorProductos}" 
                       value="0">
            </td>
            <td class="text-end">
                <strong class="text-success subtotal" data-index="${contadorProductos}">S/ 0.00</strong>
            </td>
            <td class="text-center">
                <button type="button" 
                        class="btn btn-sm btn-danger btn-eliminar" 
                        data-index="${contadorProductos}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('productosBody').appendChild(fila);

        // Event listeners
        agregarEventListeners(contadorProductos);

        contadorProductos++;
    });

    // ============================================
    // AGREGAR EVENT LISTENERS
    // ============================================
    function agregarEventListeners(index) {
        // Cambio de producto
        const selectProducto = document.querySelector(`.producto-select[data-index="${index}"]`);
        selectProducto.addEventListener('change', function() {
            const opcionSeleccionada = this.options[this.selectedIndex];
            const precio = parseFloat(opcionSeleccionada.getAttribute('data-precio')) || 0;
            
            // Actualizar precio unitario
            document.querySelector(`.precio-unitario[data-index="${index}"]`).textContent = `S/ ${precio.toFixed(2)}`;
            document.querySelector(`.precio-hidden[data-index="${index}"]`).value = precio;
            
            calcularSubtotal(index);
        });

        // Cambio de cantidad
        const inputCantidad = document.querySelector(`.cantidad-input[data-index="${index}"]`);
        inputCantidad.addEventListener('input', function() {
            calcularSubtotal(index);
        });

        // Eliminar producto
        const btnEliminar = document.querySelector(`.btn-eliminar[data-index="${index}"]`);
        btnEliminar.addEventListener('click', function() {
            document.getElementById(`producto-${index}`).remove();
            calcularTotal();

            // Si no hay productos, mostrar mensaje
            const filas = document.querySelectorAll('#productosBody .producto-row');
            if (filas.length === 0) {
                document.getElementById('emptyRow').style.display = 'table-row';
                document.getElementById('totalFooter').classList.add('d-none');
            }
        });
    }

    // ============================================
    // CALCULAR SUBTOTAL
    // ============================================
    function calcularSubtotal(index) {
        const cantidad = parseFloat(document.querySelector(`.cantidad-input[data-index="${index}"]`).value) || 0;
        const precioUnitario = parseFloat(document.querySelector(`.precio-hidden[data-index="${index}"]`).value) || 0;
        const subtotal = cantidad * precioUnitario;

        document.querySelector(`.subtotal[data-index="${index}"]`).textContent = `S/ ${subtotal.toFixed(2)}`;
        
        calcularTotal();
    }

    // ============================================
    // CALCULAR TOTAL
    // ============================================
    function calcularTotal() {
        let total = 0;
        let cantidadProductos = 0;
        let cantidadUnidades = 0;

        const subtotales = document.querySelectorAll('.subtotal');
        subtotales.forEach((subtotalElem, idx) => {
            const subtotalTexto = subtotalElem.textContent.replace('S/', '').trim();
            const subtotal = parseFloat(subtotalTexto) || 0;
            total += subtotal;

            const cantidadInput = document.querySelector(`.cantidad-input[data-index="${idx}"]`);
            if (cantidadInput) {
                cantidadUnidades += parseFloat(cantidadInput.value) || 0;
                if (subtotal > 0) cantidadProductos++;
            }
        });

        document.getElementById('totalGeneral').textContent = total.toFixed(2);
        document.getElementById('totalResumen').textContent = total.toFixed(2);
        document.getElementById('cantidadProductos').textContent = cantidadProductos;
        document.getElementById('cantidadUnidades').textContent = cantidadUnidades;
    }

    // ============================================
    // VALIDACIÓN DEL FORMULARIO
    // ============================================
    document.getElementById('formPedido').addEventListener('submit', function(e) {
        const filas = document.querySelectorAll('#productosBody .producto-row');
        
        if (filas.length === 0) {
            e.preventDefault();
            alert('⚠️ Debes agregar al menos un producto al pedido');
            return false;
        }

        // Validar que todos los productos tengan cantidad > 0
        let valido = true;
        filas.forEach(fila => {
            const cantidad = fila.querySelector('.cantidad-input').value;
            if (!cantidad || cantidad <= 0) {
                valido = false;
            }
        });

        if (!valido) {
            e.preventDefault();
            alert('⚠️ Todos los productos deben tener una cantidad válida');
            return false;
        }

        return true;
    });
</script>

</body>
</html>
