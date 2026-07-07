<?php

declare(strict_types=1);

use App\Models\User;

it('renders the admin dashboard through the shared admin layout', function (): void {
    $admin = User::factory()->create([
        'name' => 'Admin Layout Tester',
        'email' => 'admin-layout@example.test',
        'rol' => 'administrador',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Caldos &amp; Desayunos', false)
        ->assertSee('Sistema de Gestión')
        ->assertSee('Menú Principal')
        ->assertSee('Panel de Control')
        ->assertSee('href="'.route('admin.productos.index').'"', false)
        ->assertSee('Productos')
        ->assertSee('href="'.route('admin.pedidos.index').'"', false)
        ->assertSee('Pedidos')
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('Cerrar Sesión')
        ->assertSee('Dashboard - Panel de Control')
        ->assertSee('Resumen general del sistema')
        ->assertSee('Total Productos')
        ->assertSee('Admin Layout Tester');
});

it('renders the worker dashboard through the shared worker layout', function (): void {
    $worker = User::factory()->create([
        'name' => 'Worker Layout Tester',
        'email' => 'worker-layout@example.test',
        'rol' => 'trabajador',
    ]);

    $this->actingAs($worker)
        ->get(route('trabajador.dashboard'))
        ->assertOk()
        ->assertSee('Caldos & Desayunos - Panel Trabajador', false)
        ->assertSee('Trabajador')
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('Cerrar Sesión')
        ->assertSee('Dashboard - Panel de Control')
        ->assertSee('Resumen general del sistema')
        ->assertSee('Total Productos')
        ->assertSee('Top 5 - Productos Más Vendidos')
        ->assertSee('Worker Layout Tester');
});
