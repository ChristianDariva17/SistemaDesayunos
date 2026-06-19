<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder');
        }

        $usuario = $request->user();
        $allowedRoles = array_map(fn (string $role): string => $this->normalizeRole($role), $roles);
        $currentRole = $this->normalizeRole($usuario->rol);

        if (! in_array($currentRole, $allowedRoles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        return $next($request);
    }

    private function normalizeRole(string $role): string
    {
        return match ($role) {
            'admin' => 'administrador',
            'empleado' => 'trabajador',
            default => $role,
        };
    }
}
