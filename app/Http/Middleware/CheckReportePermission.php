<?php

namespace App\Http\Middleware;

class CheckReportePermission
{
    public function handle($request, Closure $next, $tipo)
    {
        if (!auth()->user()->can('ver_reporte_' . $tipo)) {
            abort(403, 'No tienes permiso para ver este reporte');
        }
        
        return $next($request);
    }
}