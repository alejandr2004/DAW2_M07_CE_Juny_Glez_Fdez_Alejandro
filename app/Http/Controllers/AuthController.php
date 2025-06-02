<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar intento de login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Verificar si la cuenta está activa
            if (!$user->active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Esta cuenta está desactivada.',
                ]);
            }

            // Verificar si la cuenta está deshabilitada
            if ($user->is_disabled) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Esta cuenta ha sido deshabilitada por un administrador. Contacte con soporte para más información.',
                ]);
            }

            // Redireccionar según el rol del usuario
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Redireccionar a usuarios clientes al catálogo de canciones
            return redirect()->intended(route('songs.index'));
        }

        throw ValidationException::withMessages([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Procesar solicitud de registro
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client', // Por defecto, todos los nuevos usuarios son clientes
            'active' => true, // Por defecto, las cuentas están activas
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
