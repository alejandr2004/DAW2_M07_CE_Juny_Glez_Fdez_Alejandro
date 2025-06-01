<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Album;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SongController extends Controller
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
     * Mostrar lista de canciones
     */
    public function index(Request $request)
    {
        // Iniciar la consulta con las relaciones necesarias
        $query = Song::query()->with(['genre']);
        
        // Filtrar por múltiples géneros (sumativo)
        if ($request->has('genres') && is_array($request->genres)) {
            $query->whereHas('genre', function($q) use ($request) {
                $q->whereIn('id', $request->genres);
            });
        }
        
        // Filtrar por artista si se proporciona
        if ($request->has('artist') && $request->artist) {
            $query->where('artist', 'like', "%{$request->artist}%");
        }
        
        // Búsqueda de texto si se proporciona (por título o nombre de artista)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        }
        
        // Ordenar las canciones por título
        $query->orderBy('title');
        
        // Preparar la selección de géneros
        $selectedGenres = $request->has('genres') && is_array($request->genres) ? $request->genres : [];
        
        // Obtener géneros y artistas para filtros
        $genres = Genre::orderBy('name')->get();
        $artists = Song::distinct()->pluck('artist');
        
        // Paginar los resultados (12 por página)
        $songs = $query->paginate(12)->withQueryString(); // withQueryString mantiene los parámetros en la paginación
        
        // Si es una petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            return response()->json([
                'html' => view('songs.partials.song-list', compact('songs'))->render(),
                'pagination' => view('partials.pagination', ['paginator' => $songs])->render(),
            ]);
        }
        
        return view('songs.index', compact('songs', 'genres', 'artists', 'selectedGenres'));
    }

    /**
     * Mostrar una canción específica
     */
    public function show(Song $song)
    {
        // Obtener playlists del usuario que incluyen esta canción
        $userPlaylists = auth()->check() 
            ? auth()->user()->playlists()->with(['songs' => function($query) use ($song) {
                $query->where('cancion_id', $song->id);
              }])->get()
            : collect();
        
        return view('songs.show', compact('song', 'userPlaylists'));
    }

    /**
     * Crear una nueva canción - Formulario
     */
    public function create()
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para añadir canciones.');
        }
        
        $albums = Album::orderBy('title')->get();
        $genres = Genre::orderBy('name')->get();
        
        return view('songs.create', compact('albums', 'genres'));
    }

    /**
     * Almacenar una nueva canción (solo admin)
     */
    public function store(Request $request)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para crear canciones.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'genre_id' => 'required|exists:generos,id',
            'duration' => 'required|string|max:10',
            'release_date' => 'required|date',
            'cover_image' => 'nullable|image|max:2048',
        ]);
        
        // Manejar la subida de la imagen de portada
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }
        
        $song = Song::create($validated);
        
        return redirect()->route('songs.show', $song)
            ->with('success', 'Canción creada correctamente.');
    }

    /**
     * Mostrar formulario para editar una canción (solo admin)
     */
    public function edit(Song $song)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para editar canciones.');
        }
        
        $albums = Album::orderBy('title')->get();
        $genres = Genre::orderBy('name')->get();
        
        return view('songs.edit', compact('song', 'albums', 'genres'));
    }

    /**
     * Actualizar una canción específica (solo admin)
     */
    public function update(Request $request, Song $song)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para editar canciones.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'genre_id' => 'required|exists:generos,id',
            'duration' => 'required|string|max:10',
            'release_date' => 'required|date',
            'cover_image' => 'nullable|image|max:2048',
        ]);
        
        // Manejar la subida de la imagen de portada
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }
        
        $song->update($validated);
        
        return redirect()->route('songs.show', $song)
            ->with('success', 'Canción actualizada correctamente.');
    }

    /**
     * Eliminar una canción específica (solo admin)
     */
    public function destroy(Song $song)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            if (request()->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            abort(403, 'No tienes permiso para eliminar canciones.');
        }
        
        // Usar transacción para eliminar la canción y actualizar relaciones
        DB::beginTransaction();
        try {
            // Eliminar de todas las playlists
            $song->playlists()->detach();
            
            // Eliminar la canción
            $song->delete();
            
            DB::commit();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Canción eliminada correctamente'
                ]);
            }
            
            return redirect()->route('admin.songs')
                            ->with('success', 'Canción eliminada correctamente.');
            return redirect()->route('songs.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return redirect()->route('songs.index')
                ->with('error', 'Error al eliminar la canción: ' . $e->getMessage());
        }
    }
    
    // Método play eliminado ya que play_count fue eliminado de la tabla
}
