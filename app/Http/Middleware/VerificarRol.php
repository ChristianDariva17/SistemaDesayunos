<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $rol  ← ESTE ES EL PARÁMETRO QUE FALTABA
     */
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        // PASO 1: Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder');
        }

        // PASO 2: Obtener el usuario autenticado
        $usuario = auth()->user();

        // PASO 3: Verificar si el usuario tiene el rol requerido
        if ($usuario->rol !== $rol) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        // PASO 4: Si todo está bien, continuar con la petición
        return $next($request);
    }
}
