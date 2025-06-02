<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArtistController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Las rutas de administración ya están protegidas en web.php
        // No es necesario definir middleware aquí
    }

    /**
     * Mostrar lista de artistas
     */
    public function index(Request $request)
    {
        // Búsqueda de texto si se proporciona
        $query = Artist::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Ordenar los artistas
        $query->orderBy('name');
        
        // Paginar los resultados
        $artists = $query->withCount('songs')->paginate(12);
        
        return view('artists.index', compact('artists'));
    }

    /**
     * Mostrar un artista específico y sus canciones
     */
    public function show(Artist $artist)
    {
        // Cargar las canciones del artista
        $artist->load('songs.genre');
        
        // Obtener los álbumes del artista si existen
        $albums = collect();
        
        return view('artists.show', compact('artist', 'albums'));
    }

    /**
     * Mostrar formulario para crear un artista (solo admin)
     */
    public function create()
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para crear artistas.');
        }
        
        return view('artists.create');
    }

    /**
     * Almacenar un nuevo artista (solo admin)
     */
    public function store(Request $request)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para crear artistas.');
        }
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'biografia' => 'nullable|string',
            'imagen' => 'nullable|image|max:2048',
            'pais' => 'nullable|string|max:100'
        ]);
        
        // Manejar la subida de la imagen pero sin guardar la ruta en la base de datos
        if ($request->hasFile('imagen')) {
            // Simplemente subimos la imagen pero no guardamos la ruta
            $request->file('imagen')->store('artists', 'public');
        }
        
        // Eliminamos el campo imagen del array validated antes de crear el artista
        // ya que este campo no existe en la tabla de la base de datos
        if (isset($validated['imagen'])) {
            unset($validated['imagen']);
        }
        
        $artist = Artist::create($validated);
        
        return redirect()->route('artists.show', $artist)
            ->with('success', 'Artista creado correctamente.');
    }

    /**
     * Mostrar formulario para editar un artista (solo admin)
     */
    public function edit(Artist $artist)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para editar artistas.');
        }
        
        return view('artists.edit', compact('artist'));
    }

    /**
     * Actualizar un artista específico (solo admin)
     */
    public function update(Request $request, Artist $artist)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para editar artistas.');
        }
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'biografia' => 'nullable|string',
            'imagen' => 'nullable|image|max:2048',
            'pais' => 'nullable|string|max:100'
        ]);
        
        // Manejar la subida de la imagen
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('artists', 'public');
            $validated['imagen'] = $path;
        }
        
        $artist->update($validated);
        
        return redirect()->route('artists.show', $artist)
            ->with('success', 'Artista actualizado correctamente.');
    }

    /**
     * Eliminar un artista específico (solo admin)
     */
    public function destroy(Artist $artist)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'No tienes permiso para eliminar artistas.'], 403);
            }
            abort(403, 'No tienes permiso para eliminar artistas.');
        }
        
        try {
            // Iniciar transacción
            \DB::beginTransaction();
            
            // Verificar si el artista tiene canciones
            $songCount = $artist->songs()->count();
            if ($songCount > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false, 
                        'error' => "No se puede eliminar el artista '$artist->nombre' porque tiene $songCount canciones asociadas."
                    ]);
                }
                return back()->with('error', 'No se puede eliminar el artista porque tiene canciones asociadas.');
            }
            
            // Guardar el nombre para el mensaje
            $artistName = $artist->nombre;
            
            // Eliminar el artista
            $artist->delete();
            
            \DB::commit();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => "Artista '$artistName' eliminado correctamente."
                ]);
            }
            
            return redirect()->route('artists.index')
                ->with('success', 'Artista eliminado correctamente.');
        } catch (\Exception $e) {
            \DB::rollBack();
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Error al eliminar el artista: ' . $e->getMessage()]);
            }
            
            return redirect()->route('artists.index')
                ->with('error', 'Error al eliminar el artista: ' . $e->getMessage());
        }
    }
}
