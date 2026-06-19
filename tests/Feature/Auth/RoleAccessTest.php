<?php

use App\Models\User;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReporteController as AdminReporteController;
use App\Http\Controllers\Trabajador\DashboardController as TrabajadorDashboardController;
use Illuminate\Support\Facades\DB;

test('administrators can access the real admin dashboard route', function (): void {
    $this->app->instance(AdminDashboardController::class, new class extends AdminDashboardController
    {
        public function index()
        {
            return response('admin ok');
        }
    });

    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertOk();
});

test('workers can access the real worker dashboard route', function (): void {
    $this->app->instance(TrabajadorDashboardController::class, new class extends TrabajadorDashboardController
    {
        public function index()
        {
            return response('worker ok');
        }
    });

    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $this->actingAs($user)
        ->get('/trabajador/dashboard')
        ->assertOk();
});

test('guests are redirected to login from protected role dashboards', function (): void {
    $this->get('/admin/dashboard')->assertRedirect(route('login', absolute: false));

    $this->get('/trabajador/dashboard')->assertRedirect(route('login', absolute: false));
});

test('workers cannot access the real admin dashboard route', function (): void {
    $user = User::factory()->create([
        'rol' => 'trabajador',
    ]);

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

test('administrators cannot access the worker dashboard route', function (): void {
    $user = User::factory()->create([
        'rol' => 'administrador',
    ]);

    $this->actingAs($user)
        ->get('/trabajador/dashboard')
        ->assertForbidden();
});

test('report routes are protected by auth and admin role middleware', function (): void {
    $route = app('router')->getRoutes()->getByName('admin.reportes.index');
    $middleware = $route?->gatherMiddleware() ?? [];

    expect($middleware)->toContain('auth');
    expect($middleware)->toContain('rol:administrador');
    expect($middleware)->not()->toContain('reporte:index');
});

test('admin and worker route groups include valid role middleware before role checks', function (): void {
    $adminMiddleware = app('router')->getRoutes()->getByName('admin.dashboard')?->gatherMiddleware() ?? [];
    $workerMiddleware = app('router')->getRoutes()->getByName('trabajador.dashboard')?->gatherMiddleware() ?? [];

    expect($adminMiddleware)->toContain('auth');
    expect($adminMiddleware)->toContain('valid.role');
    expect($adminMiddleware)->toContain('rol:administrador');
    expect(array_search('auth', $adminMiddleware, true))->toBeLessThan(array_search('valid.role', $adminMiddleware, true));
    expect(array_search('valid.role', $adminMiddleware, true))->toBeLessThan(array_search('rol:administrador', $adminMiddleware, true));

    expect($workerMiddleware)->toContain('auth');
    expect($workerMiddleware)->toContain('valid.role');
    expect($workerMiddleware)->toContain('rol:trabajador');
    expect(array_search('auth', $workerMiddleware, true))->toBeLessThan(array_search('valid.role', $workerMiddleware, true));
    expect(array_search('valid.role', $workerMiddleware, true))->toBeLessThan(array_search('rol:trabajador', $workerMiddleware, true));
});

test('unsupported authenticated roles are logged out when hitting admin and worker areas', function (): void {
    $connection = DB::connection();

    if ($connection->getDriverName() === 'sqlite') {
        $connection->statement('PRAGMA ignore_check_constraints = ON');
    }

    try {
        $user = User::factory()->create();
        DB::table('users')->where('id', $user->id)->update([
            'rol' => 'supervisor',
        ]);
        $user->refresh();
    } finally {
        if ($connection->getDriverName() === 'sqlite') {
            $connection->statement('PRAGMA ignore_check_constraints = OFF');
        }
    }

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();

    $this->actingAs($user)
        ->get('/trabajador/dashboard')
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();
});

test('administrators can access the report index route', function (): void {
    $this->app->instance(AdminReporteController::class, new class extends AdminReporteController
    {
        public function index()
        {
            return response('report ok');
        }
    });

    $user = User::factory()->make([
        'rol' => 'administrador',
    ]);

    $this->actingAs($user)
        ->get('/admin/reportes')
        ->assertOk();
});

test('workers cannot access the report index route', function (): void {
    $user = User::factory()->make([
        'rol' => 'trabajador',
    ]);

    $this->actingAs($user)
        ->get('/admin/reportes')
        ->assertForbidden();
});
