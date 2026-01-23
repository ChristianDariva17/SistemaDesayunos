<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Mostrar el formulario de login
     */
    public function showLoginForm()
    {
        // Si ya está autenticado, redirigir al dashboard según el rol
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('auth.login');
    }

    /**
     * Procesar el login
     */
    public function login(Request $request)
    {
        // Validar las credenciales
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Intentar autenticar
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // Regenerar la sesión para prevenir session fixation
            $request->session()->regenerate();

            // Log de auditoría
            Log::info('Usuario autenticado', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'rol' => Auth::user()->rol,
                'ip' => $request->ip(),
            ]);

            // Redirigir al dashboard según el rol
            return $this->redirectToDashboard();
        }

        // Log de intento fallido
        Log::warning('Intento de login fallido', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        // Credenciales incorrectas
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        // Log de auditoría antes de cerrar sesión
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
    private function redirectToDashboard()
    {
        $user = Auth::user();

        // Verificar el rol y redirigir
        switch ($user->rol) {
            case 'admin':
            case 'administrador':
                return redirect()->route('admin.dashboard');

            case 'trabajador':
            case 'empleado':
                return redirect()->route('trabajador.dashboard');

            default:
                // Si el rol no es reconocido, cerrar sesión y mostrar error
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Tu cuenta no tiene un rol válido. Contacta al administrador.',
                ]);
        }
    }

    /**
     * Muestra el formulario de registro
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Procesa el registro de un nuevo usuario
     */
    public function register(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:admin,trabajador',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'rol.required' => 'Debe seleccionar un rol',
            'rol.in' => 'El rol seleccionado no es válido',
        ]);

        try {
            // Crear el nuevo usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol' => $request->rol,
            ]);

            // Autenticar automáticamente al usuario
            Auth::login($user);

            // Redirigir según el rol
            if ($user->rol === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('success', '¡Registro exitoso! Bienvenido al sistema.');
            } elseif ($user->rol === 'trabajador') {
                return redirect()->route('trabajador.dashboard')
                    ->with('success', '¡Registro exitoso! Bienvenido al sistema.');
            }

            // Por defecto, redirigir al dashboard de admin
            return redirect()->route('admin.dashboard')
                ->with('success', '¡Registro exitoso! Bienvenido al sistema.');
        } catch (\Exception $e) {
            Log::error('Error en registro: ' . $e->getMessage());

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Error al registrar el usuario. Por favor, intente nuevamente.');
        }
    }
}
