<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| ⚠️ IMPORTS DE CONTROLADORES - OBLIGATORIOS
|--------------------------------------------------------------------------
*/

// Autenticación
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

// Controladores de ADMINISTRADOR (con alias "Admin" para evitar conflictos)
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\Admin\ClienteController as AdminClienteController;
use App\Http\Controllers\Admin\PedidoController as AdminPedidoController;
use App\Http\Controllers\Admin\EmpleadoController as AdminEmpleadoController;
use App\Http\Controllers\Admin\ReporteController as AdminReporteController;
use App\Http\Controllers\Admin\StockEntryController as AdminStockEntryController;
use App\Http\Controllers\Admin\StockAdjustmentController as AdminStockAdjustmentController;

// Controladores de TRABAJADOR (con alias "Trabajador" para evitar conflictos)
use App\Http\Controllers\Trabajador\DashboardController as TrabajadorDashboardController;
use App\Http\Controllers\Trabajador\ProductoController as TrabajadorProductoController;
use App\Http\Controllers\Trabajador\ClienteController as TrabajadorClienteController;
use App\Http\Controllers\Trabajador\PedidoController as TrabajadorPedidoController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Sin autenticación)
|--------------------------------------------------------------------------
*/

// Redirigir la raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth', 'valid.role'])->name('logout');

Route::middleware(['auth', 'valid.role'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dashboard genérico post-autenticación
Route::get('/dashboard', function (Request $request) {
    $user = auth()->user();

    if (in_array($user?->rol, ['administrador', 'admin'], true)) {
        return redirect()->route('admin.dashboard');
    }

    if (in_array($user?->rol, ['trabajador', 'empleado'], true)) {
        return redirect()->route('trabajador.dashboard');
    }

    Auth::guard()->logoutCurrentDevice();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->withErrors([
        'email' => 'Tu cuenta no tiene un rol válido. Contacta al administrador.',
    ]);
})->middleware(['auth', 'valid.role'])->name('dashboard');

/*
| Rutas de ADMINISTRADOR (Requieren autenticación + rol admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'valid.role', 'rol:administrador'])->group(function () {

    // ══════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
         ->name('dashboard');

    Route::view('/configuracion', 'admin.settings.index')
         ->name('settings.index');

    // ══════════════════════════════════════════════════════════════════
    // PRODUCTOS - CRUD COMPLETO
    // ══════════════════════════════════════════════════════════════════

    // Rutas especiales ANTES del resource
    Route::get('productos/buscar', [AdminProductoController::class, 'buscar'])
         ->name('productos.buscar');

    Route::get('productos/exportar', [AdminProductoController::class, 'exportar'])
         ->name('productos.exportar');

    Route::get('productos/estadisticas', [AdminProductoController::class, 'estadisticas'])
         ->name('productos.estadisticas');

    // Resource: index, create, store, show, edit, update, destroy
    Route::resource('productos', AdminProductoController::class);

    // Rutas adicionales
    Route::patch('productos/{producto}/actualizar-stock', [AdminProductoController::class, 'actualizarStock'])
         ->name('productos.actualizar-stock');

    Route::patch('productos/{producto}/toggle-estado', [AdminProductoController::class, 'toggleEstado'])
         ->name('productos.toggle-estado');

    Route::post('productos/{producto}/duplicar', [AdminProductoController::class, 'duplicar'])
          ->name('productos.duplicar');

    Route::get('stock-entries/create', [AdminStockEntryController::class, 'create'])
         ->name('stock-entries.create');

    Route::post('stock-entries', [AdminStockEntryController::class, 'store'])
         ->name('stock-entries.store');

    Route::get('stock-adjustments/create', [AdminStockAdjustmentController::class, 'create'])
         ->name('stock-adjustments.create');

    Route::post('stock-adjustments', [AdminStockAdjustmentController::class, 'store'])
         ->name('stock-adjustments.store');

    // ══════════════════════════════════════════════════════════════════
    // CLIENTES - CRUD COMPLETO
    // ══════════════════════════════════════════════════════════════════

    // Rutas especiales ANTES del resource
    Route::get('clientes/buscar', [AdminClienteController::class, 'buscar'])
         ->name('clientes.buscar');

    Route::get('clientes/exportar', [AdminClienteController::class, 'exportar'])
         ->name('clientes.exportar');

    Route::get('clientes/estadisticas', [AdminClienteController::class, 'estadisticas'])
         ->name('clientes.estadisticas');

    // Resource
    Route::resource('clientes', AdminClienteController::class);

    // Rutas adicionales
    Route::patch('clientes/{cliente}/toggle-estado', [AdminClienteController::class, 'toggleEstado'])
         ->name('clientes.toggle-estado');

    Route::post('clientes/{cliente}/duplicar', [AdminClienteController::class, 'duplicar'])
         ->name('clientes.duplicar');

    // ══════════════════════════════════════════════════════════════════
    // PEDIDOS - CRUD COMPLETO
    // ══════════════════════════════════════════════════════════════════

    // Rutas especiales ANTES del resource
    Route::get('pedidos/exportar', [AdminPedidoController::class, 'exportar'])
         ->name('pedidos.exportar');

    // Resource
    Route::resource('pedidos', AdminPedidoController::class);

    // Rutas adicionales
    Route::patch('pedidos/{pedido}/cambiar-estado', [AdminPedidoController::class, 'cambiarEstado'])
         ->name('pedidos.cambiar-estado');

    Route::get('pedidos/{pedido}/imprimir', [AdminPedidoController::class, 'imprimir'])
         ->name('pedidos.imprimir');

    Route::post('pedidos/{pedido}/duplicar', [AdminPedidoController::class, 'duplicar'])
         ->name('pedidos.duplicar');

    // ══════════════════════════════════════════════════════════════════
    // EMPLEADOS - CRUD COMPLETO (Solo Admin)
    // ══════════════════════════════════════════════════════════════════
    Route::resource('empleados', AdminEmpleadoController::class);

    // ══════════════════════════════════════════════════════════════════
    // REPORTES (Solo Admin)
    // ══════════════════════════════════════════════════════════════════
    Route::prefix('reportes')->name('reportes.')->group(function () {
        // Vista principal de reportes
        Route::get('/', [AdminReporteController::class, 'index'])
             ->name('index');

        // Reporte de inventario completo
        Route::get('/inventario', [AdminReporteController::class, 'inventario'])
             ->name('inventario');

        // Reporte de stock bajo
        Route::get('/stock-bajo', [AdminReporteController::class, 'stockBajo'])
             ->name('stock-bajo');

        // Reporte de movimientos de stock
        Route::get('/stock-movimientos', [AdminReporteController::class, 'stockMovimientos'])
             ->name('stock-movimientos');

        // Resumen de inventario por producto
        Route::get('/resumen-inventario', [AdminReporteController::class, 'resumenInventario'])
             ->name('resumen-inventario');

        // Reporte de ventas
        Route::get('/ventas', [AdminReporteController::class, 'ventas'])
             ->name('ventas');

        // Reporte de ventas por cliente
        Route::get('/ventas-por-cliente', [AdminReporteController::class, 'ventasPorCliente'])
             ->name('ventas-por-cliente');
    });

});

/*
|--------------------------------------------------------------------------
| Rutas de TRABAJADOR (Requieren autenticación + rol trabajador)
|--------------------------------------------------------------------------
*/

Route::prefix('trabajador')->name('trabajador.')->middleware(['auth', 'valid.role', 'rol:trabajador'])->group(function () {

    // ══════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════
    Route::get('/dashboard', [TrabajadorDashboardController::class, 'index'])
         ->name('dashboard');

    // ══════════════════════════════════════════════════════════════════
    // PRODUCTOS - SOLO LECTURA
    // ══════════════════════════════════════════════════════════════════
    Route::get('productos', [TrabajadorProductoController::class, 'index'])
         ->name('productos.index');

    Route::get('productos/{producto}', [TrabajadorProductoController::class, 'show'])
         ->name('productos.show');

    // ══════════════════════════════════════════════════════════════════
    // CLIENTES - SOLO LECTURA
    // ══════════════════════════════════════════════════════════════════
    Route::get('clientes', [TrabajadorClienteController::class, 'index'])
         ->name('clientes.index');

    Route::get('clientes/{cliente}', [TrabajadorClienteController::class, 'show'])
         ->name('clientes.show');

    // ══════════════════════════════════════════════════════════════════
    // PEDIDOS - LECTURA Y CREACIÓN
    // ══════════════════════════════════════════════════════════════════
    Route::get('pedidos', [TrabajadorPedidoController::class, 'index'])
         ->name('pedidos.index');

    Route::get('pedidos/create', [TrabajadorPedidoController::class, 'create'])
         ->name('pedidos.create');

    Route::post('pedidos', [TrabajadorPedidoController::class, 'store'])
         ->name('pedidos.store');

    Route::post('pedidos/{pedido}/duplicar', [TrabajadorPedidoController::class, 'duplicar'])
         ->name('pedidos.duplicar');

    Route::delete('pedidos/{pedido}', [TrabajadorPedidoController::class, 'destroy'])
         ->name('pedidos.destroy');

    Route::get('pedidos/{pedido}', [TrabajadorPedidoController::class, 'show'])
         ->name('pedidos.show');

});
