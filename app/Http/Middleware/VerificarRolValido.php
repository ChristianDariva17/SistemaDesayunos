<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerificarRolValido
{
    private const VALID_ROLES = ['administrador', 'trabajador'];

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder');
        }

        $currentRole = $this->normalizeRole((string) $usuario->rol);

        if (! in_array($currentRole, self::VALID_ROLES, true)) {
            Log::warning('Unsupported role session rejected', [
                'user_id' => $usuario->id,
                'role' => $usuario->rol,
                'path' => $request->path(),
            ]);

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta no tiene un rol válido. Contacta al administrador.',
            ]);
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
