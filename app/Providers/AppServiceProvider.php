<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\DailyCashClosure;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Policies\ClientePolicy;
use App\Policies\DailyCashClosurePolicy;
use App\Policies\EmpleadoPolicy;
use App\Policies\PedidoPolicy;
use App\Policies\ProductoPolicy;
use App\View\Composers\AppLayoutComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Pedido::class, PedidoPolicy::class);
        Gate::policy(Producto::class, ProductoPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(Empleado::class, EmpleadoPolicy::class);
        Gate::policy(DailyCashClosure::class, DailyCashClosurePolicy::class);

        View::composer('layouts.app', AppLayoutComposer::class);
    }
}
