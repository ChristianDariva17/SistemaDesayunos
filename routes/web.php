<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ClienteController;


Route::get('/', fn () => to_route('login'));

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('empleados', EmpleadoController::class)->except(['show']);
});

require __DIR__.'/auth.php';



Route::resource('productos', ProductoController::class);

Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');


/*Route::get('/dashboard/pdf', [DashboardController::class, 'exportarPDF'])->name('dashboard.pdf');*/

    // Rutas para pedidos
    Route::resource('pedidos', PedidoController::class);
    Route::get('/pedidos/{pedido}/imprimir', [PedidoController::class, 'imprimir'])->name('pedidos.imprimir');

    // Rutas para clientes
    Route::resource('clientes', ClienteController::class);
