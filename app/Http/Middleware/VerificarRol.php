<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\RoleNormalizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder');
        }

        $usuario = $request->user();
        $allowedRoles = RoleNormalizer::normalizeMany($roles);
        $currentRole = RoleNormalizer::normalize((string) $usuario->rol);

        if (! in_array($currentRole, $allowedRoles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        return $next($request);
    }
}
