<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const ALLOWED_ROLES = ['administrador', 'trabajador'];

    /**
     * Mostrar el formulario de login
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('auth.login');
    }

    /**
     * Procesar el login
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $role = $this->normalizeRole((string) Auth::user()->rol);

            if (! in_array($role, self::ALLOWED_ROLES, true)) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Tu cuenta no tiene un rol válido. Contacta al administrador.',
                ])->onlyInput('email');
            }

            Log::info('Usuario autenticado', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'rol' => $role,
                'ip' => $request->ip(),
            ]);

            return $this->redirectToDashboard();
        }

        Log::warning('Intento de login fallido', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): RedirectResponse
    {
        Log::info('Usuario cerró sesión', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'rol' => Auth::user()->rol,
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Has cerrado sesión correctamente.');
    }

    /**
     * Redirigir al dashboard según el rol del usuario
     */
    private function redirectToDashboard(): RedirectResponse
    {
        return redirect()->route('dashboard');
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
