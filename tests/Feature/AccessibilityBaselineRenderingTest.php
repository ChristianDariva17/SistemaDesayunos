<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('renders admin and worker layouts with skip links, landmarks, and named navigation controls', function (): void {
    $admin = User::factory()->create([
        'name' => 'Admin Accessibility Tester',
        'email' => 'admin-accessibility@example.test',
        'rol' => 'administrador',
    ]);

    $worker = User::factory()->create([
        'name' => 'Worker Accessibility Tester',
        'email' => 'worker-accessibility@example.test',
        'rol' => 'trabajador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('href="#main-content" class="visually-hidden-focusable"', false)
        ->assertSee('<main id="main-content"', false)
        ->assertSee('<aside id="sidebar-wrapper" aria-label="Navegación de administración">', false)
        ->assertSee('aria-label="Menú principal de administración"', false)
        ->assertSee('aria-label="Alternar menú lateral"', false)
        ->assertSee('aria-label="Ver notificaciones"', false)
        ->assertSee('aria-label="Abrir menú de usuario"', false);

    $this->actingAs($worker)
        ->get(route('trabajador.dashboard'))
        ->assertOk()
        ->assertSee('href="#main-content" class="visually-hidden-focusable"', false)
        ->assertSee('<main id="main-content"', false)
        ->assertSee('aria-label="Barra superior de trabajador"', false);
});

it('renders icon-only product actions with accessible names', function (): void {
    Storage::fake('public');
    $admin = User::factory()->create([
        'email' => 'admin-product-a11y@example.test',
        'rol' => 'administrador',
    ]);

    $producto = Producto::create([
        'nombre' => 'Tamales accesibles',
        'descripcion' => 'Producto de prueba',
        'categoria' => 'comidas',
        'precio' => 9.50,
        'sku' => 'A11Y-001',
        'stock' => 3,
        'stock_minimo' => 5,
        'estado' => 'activo',
        'imagen' => 'productos/tamal.jpg',
    ]);
    Storage::disk('public')->put($producto->imagen, 'image');

    $this->actingAs($admin)
        ->get(route('admin.productos.index'))
        ->assertOk()
        ->assertSee('aria-label="Cambiar estado de '.$producto->nombre.'"', false)
        ->assertSee('aria-label="Ver detalles de '.$producto->nombre.'"', false)
        ->assertSee('aria-label="Editar '.$producto->nombre.'"', false)
        ->assertSee('aria-label="Actualizar stock de '.$producto->nombre.'"', false)
        ->assertSee('aria-label="Duplicar '.$producto->nombre.'"', false)
        ->assertSee('aria-label="Eliminar '.$producto->nombre.'"', false)
        ->assertSee('<button type="button"', false)
        ->assertSee('aria-label="Ver imagen de '.$producto->nombre.'"', false)
        ->assertSee('aria-hidden="true"', false);
});

it('renders order forms with live totals and accessible dynamic quantity controls', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-order-a11y@example.test',
        'rol' => 'administrador',
    ]);

    $worker = User::factory()->create([
        'email' => 'worker-order-a11y@example.test',
        'rol' => 'trabajador',
    ]);

    Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.a11y@example.test',
        'estado' => 'activo',
    ]);

    Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    Producto::create([
        'nombre' => 'Sandwich accesible',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk()
        ->assertSee('aria-live="polite"', false);

    $this->actingAs($worker)
        ->get(route('trabajador.pedidos.create'))
        ->assertOk()
        ->assertSee('href="#main-content" class="visually-hidden-focusable"', false)
        ->assertSee('<main id="main-content"', false)
        ->assertSee('aria-live="polite"', false);
});

it('renders production order row templates with accessible dynamic labels', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-order-template-a11y@example.test',
        'rol' => 'administrador',
    ]);

    $worker = User::factory()->create([
        'email' => 'worker-order-template-a11y@example.test',
        'rol' => 'trabajador',
    ]);

    $cliente = Cliente::create([
        'nombre' => 'Ana',
        'apellido' => 'Paredes',
        'email' => 'ana.template-a11y@example.test',
        'estado' => 'activo',
    ]);

    $empleado = Empleado::create([
        'nombre' => 'Luis Gomez',
        'rol_operativo' => 'mesero',
        'estado' => 'activo',
    ]);

    $producto = Producto::create([
        'nombre' => 'Sandwich accesible',
        'categoria' => 'desayuno',
        'stock' => 10,
        'estado' => 'activo',
        'precio' => 12.50,
    ]);

    $pedido = \App\Models\Pedido::create([
        'cliente_id' => $cliente->id,
        'empleado_id' => $empleado->id,
        'fecha' => now()->toDateString(),
        'hora' => '08:30',
        'total' => 25.00,
        'estado' => 'pendiente',
    ]);

    $pedido->productos()->attach($producto->id, [
        'cantidad' => 2,
        'precio_unitario' => 12.50,
        'subtotal' => 25.00,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.create'))
        ->assertOk()
        ->assertSee('aria-label="Reducir cantidad de la fila ${producto.index + 1}"', false)
        ->assertSee('aria-label="Cantidad de la fila ${producto.index + 1}"', false)
        ->assertSee('aria-label="Aumentar cantidad de la fila ${producto.index + 1}"', false)
        ->assertSee('aria-label="Eliminar producto de la fila ${producto.index + 1}"', false);

    $this->actingAs($admin)
        ->get(route('admin.pedidos.edit', $pedido))
        ->assertOk()
        ->assertSee('aria-label="Reducir cantidad de la fila ${producto.index + 1}"', false)
        ->assertSee('aria-label="Cantidad de la fila ${producto.index + 1}"', false)
        ->assertSee('aria-label="Aumentar cantidad de la fila ${producto.index + 1}"', false)
        ->assertSee('aria-label="Eliminar producto de la fila ${producto.index + 1}"', false);

    $this->actingAs($worker)
        ->get(route('trabajador.pedidos.create'))
        ->assertOk()
        ->assertSee('aria-label="Producto de la fila ${contadorProductos + 1}"', false)
        ->assertSee('aria-label="Cantidad de la fila ${contadorProductos + 1}"', false)
        ->assertSee('aria-label="Eliminar producto de la fila ${contadorProductos + 1}"', false);
});
