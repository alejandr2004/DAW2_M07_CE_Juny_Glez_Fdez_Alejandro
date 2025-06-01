<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Constructor - no aplicamos middleware aquí porque
     * ya lo estamos aplicando en las rutas
     */
    public function __construct()
    {
        // El middleware auth ya se aplica en las rutas
    }

    /**
     * Muestra el panel de administrador
     */
    public function dashboard(Request $request)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'No tienes permisos para acceder al panel de administración.');
        }
        
        return view('admin.dashboard');
    }
}
