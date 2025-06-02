<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Album;
use App\Models\Genre;
use App\Models\Artist;
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
     * Método para obtener canciones en formato JSON (para AJAX)
     */
    public function getSongs(Request $request)
    {
        try {
            // 1. Verificación de permisos
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'No tienes permiso para acceder a esta sección',
                    'songs' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'total' => 0,
                        'per_page' => 10,
                    ]
                ], 403);
            }
            
            // 2. Construir la consulta base con joins necesarios
            $query = Song::with(['genre', 'artist', 'album']);
            
            // 3. Aplicar filtros dinámicos desde la petición
            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }
            
            if ($request->filled('artist')) {
                $query->whereHas('artist', function($q) use ($request) {
                    // Nota: Usamos 'nombre' en lugar de 'name' debido a la inconsistencia entre
                    // los nombres de campos en la tabla 'artistas'
                    $q->where('nombre', 'like', '%' . $request->artist . '%');
                });
            }
            
            if ($request->filled('genre')) {
                $query->where('genre_id', $request->genre);
            }
            
            // 4. Paginación de resultados
            $perPage = (int) $request->input('perPage', 10);
            $songs = $query->paginate($perPage);
            
            // 5. Devolver respuesta estructurada en JSON
            return response()->json([
                'songs' => $songs->items(),
                'pagination' => [
                    'current_page' => $songs->currentPage(),
                    'last_page' => $songs->lastPage(),
                    'total' => $songs->total(),
                    'per_page' => $songs->perPage(),
                ]
            ]);
        } catch (\Exception $e) {
            // 6. Manejo de errores estructurado
            return response()->json([
                'error' => 'Ha ocurrido un error: ' . $e->getMessage(),
                'songs' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => 10,
                ]
            ], 500);
        }
    }
    
    /**
     * Actualizar una canción mediante petición AJAX
     */
    public function updateAjax(Request $request, Song $song)
    {
        try {
            // 1. Verificar si el usuario es admin
            if (auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para editar canciones.'
                ], 403);
            }
            
            // 2. Validación de datos
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'artist' => 'required|string|max:255',
                'genre_id' => 'required|exists:generos,id',
                'duration' => 'required|string|max:10',
                'release_date' => 'required|date',
                'cover_image' => 'nullable|image|max:2048',
            ]);
            
            // 3. Manejar la subida de la imagen de portada
            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('covers', 'public');
                $validated['cover_image'] = $path;
            }
            
            // 4. Actualizar datos
            $song->update($validated);
            
            // 5. Devolver respuesta de éxito
            return response()->json([
                'success' => true,
                'message' => 'Canción actualizada correctamente',
                'song' => $song->load(['artist', 'genre', 'album'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 6. Manejar errores de validación
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // 7. Manejar otros errores
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la canción',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar una canción mediante petición AJAX
     */
    public function destroyAjax(Song $song)
    {
        try {
            // 1. Verificar si el usuario es admin
            if (auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar canciones.'
                ], 403);
            }
            
            // 2. Usar transacción para eliminar la canción y actualizar relaciones
            DB::beginTransaction();
            
            // 3. Eliminar de todas las playlists
            $song->playlists()->detach();
            
            // 4. Eliminar la canción
            $song->delete();
            
            DB::commit();
            
            // 5. Devolver respuesta de éxito
            return response()->json([
                'success' => true,
                'message' => 'Canción eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            // 6. Manejar errores
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la canción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar lista de canciones
     */
    public function index(Request $request)
    {
        // Verificar que la petición POST desde AJAX incluya un token CSRF válido
        if ($request->isMethod('post') && !$request->ajax()) {
            abort(403, 'Acceso no autorizado');
        }
        
        // Iniciar la consulta con las relaciones necesarias
        $query = Song::query()->with(['genre', 'artist']);
        
        // Filtrar por múltiples géneros (sumativo)
        if ($request->has('genres') && is_array($request->genres)) {
            $query->whereHas('genre', function($q) use ($request) {
                $q->whereIn('id', $request->genres);
            });
        }
        
        // Filtrar por artista si se proporciona
        if ($request->has('artist') && $request->artist) {
            $query->whereHas('artist', function($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->artist}%");
            });
        }
        
        // Búsqueda de texto si se proporciona (por título o nombre de artista)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('artist', function($artistQuery) use ($search) {
                      $artistQuery->where('nombre', 'like', "%{$search}%");
                  });
            });
        }
        
        // Ordenar las canciones por título
        $query->orderBy('title');
        
        // Preparar la selección de géneros
        $selectedGenres = $request->has('genres') && is_array($request->genres) ? $request->genres : [];
        
        // Obtener géneros y artistas para filtros
        $genres = Genre::orderBy('name')->get();
        $artists = Artist::orderBy('nombre')->get(); // Obtenemos objetos completos, no solo nombres
        
        // Paginar los resultados (12 por página)
        $songs = $query->paginate(12)->withQueryString(); // withQueryString mantiene los parámetros en la paginación
        
        // Si es una petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            return response()->json([
                'html' => view('songs.partials.song-list', compact('songs'))->render()
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
                $query->where('song_id', $song->id);
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
        
        // Para no modificar la base de datos o el modelo, necesitamos agregar
        // los campos obligatorios album_id y artist_id
        // Buscamos un artista existente o creamos uno temporal
        $artist = Artist::where('nombre', $validated['artist'])->first();
        if (!$artist) {
            // Si no existe el artista, usamos el primer artista existente
            $artist = Artist::first();
            if (!$artist) {
                // Si no hay artistas, redirigimos con error
                return redirect()->back()->withErrors(['artist' => 'No hay artistas disponibles en el sistema.']);
            }
        }
        
        // Buscamos un álbum existente o usamos uno temporal
        $album = Album::first();
        if (!$album) {
            // Si no hay álbumes, redirigimos con error
            return redirect()->back()->withErrors(['album' => 'No hay álbumes disponibles en el sistema.']);
        }
        
        // Agregamos los IDs requeridos al array validado
        $validated['artist_id'] = $artist->id;
        $validated['album_id'] = $album->id;
        
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
        try {
            // Verificar si el usuario es admin
            if (auth()->user()->role !== 'admin') {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para editar canciones.'
                    ], 403);
                }
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
            
            // Recargar relaciones para tener datos actualizados
            $song->load(['genre', 'artist']);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Canción actualizada correctamente.',
                    'song' => $song
                ]);
            }
            
            return redirect()->route('songs.show', $song)
                ->with('success', 'Canción actualizada correctamente.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ha ocurrido un error: ' . $e->getMessage(),
                    'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null
                ], 422);
            }
            
            return back()->withErrors(['error' => 'Error al actualizar la canción: ' . $e->getMessage()])->withInput();
        }
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
