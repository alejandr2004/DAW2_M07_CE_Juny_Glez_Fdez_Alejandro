<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Las rutas de administración ya están protegidas en web.php
    }

    /**
     * Mostrar lista de álbumes
     */
    public function index(Request $request)
    {
        // Iniciar la consulta con las relaciones necesarias
        $query = Album::with(['artist', 'songs']);
        
        // Filtrar por artista si se proporciona
        if ($request->has('artist') && $request->artist) {
            $query->where('artist_id', $request->artist);
        }
        
        // Búsqueda de texto si se proporciona
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('artist', function($q) use ($search) {
                       $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Ordenar los álbumes
        $query->orderBy('title');
        
        // Paginar los resultados
        $albums = $query->paginate(12)->withQueryString();
        
        // Obtener artistas para filtros
        $artists = Artist::orderBy('name')->get();
        
        return view('albums.index', compact('albums', 'artists'));
    }

    /**
     * Mostrar un álbum específico
     */
    public function show(Album $album)
    {
        $album->load(['artist', 'songs.genre']);
        
        return view('albums.show', compact('album'));
    }

    /**
     * Mostrar formulario para crear un álbum (solo admin)
     */
    public function create()
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para crear álbumes.');
        }
        
        // Obtener artistas para el formulario
        $artists = Artist::orderBy('nombre')->get();
        
        return view('albums.create', compact('artists'));
    }

    /**
     * Almacenar un nuevo álbum (solo admin)
     */
    public function store(Request $request)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            abort(403, 'No tienes permiso para crear álbumes.');
        }
        
        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'artist_id' => 'required|exists:artistas,id',
            'genre_id' => 'required|exists:generos,id',
            'release_date' => 'required|date',
            'cover_image' => 'nullable|image|max:2048',
            'temp_cover_path' => 'nullable|string',
            'songs' => 'nullable|array',
            'songs.*.title' => 'required|string|max:255',
            'songs.*.duration' => 'required|string|max:10',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Empezar transacción para garantizar integridad
        DB::beginTransaction();
        
        try {
            $albumData = [
                'title' => $request->title,
                'artist_id' => $request->artist_id,
                'genre_id' => $request->genre_id,
                'release_date' => $request->release_date,
            ];
            
            // Procesamiento de imagen
            if ($request->has('temp_cover_path') && !empty($request->temp_cover_path)) {
                // Si se subió previamente una imagen temporal, usarla
                $albumData['cover_image'] = $request->temp_cover_path;
            } elseif ($request->hasFile('cover_image')) {
                // Si se subió una imagen directamente
                $albumData['cover_image'] = $request->file('cover_image')->store('covers', 'public');
            }
            
            $album = Album::create($albumData);
            
            // Procesar canciones si se enviaron
            if ($request->has('songs') && is_array($request->songs)) {
                foreach ($request->songs as $songData) {
                    if (!empty($songData['title']) && !empty($songData['duration'])) {
                        // Crear cada canción para este álbum
                        $song = new \App\Models\Song([  
                            'title' => $songData['title'],
                            'duration' => $songData['duration'],
                            'genre_id' => $request->genre_id, // Usar el mismo género del álbum
                            'cover_image' => 'songs/default.jpg', // Imagen por defecto
                            'play_count' => 0
                        ]);
                        
                        $song->album_id = $album->id;
                        $song->artist_id = $request->artist_id; // Usar el mismo artista del álbum
                        $song->save();
                    }
                }
            }
            
            // Commit de la transacción
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Álbum creado correctamente',
                    'redirect' => route('admin.albums')
                ]);
            }
            
            return redirect()->route('admin.albums')
                            ->with('success', 'Álbum creado correctamente.');
                            
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el álbum: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al crear el álbum: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar formulario para editar un álbum (solo admin)
     */
    public function edit(Album $album)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para editar álbumes.');
        }
        
        // Cargar las relaciones necesarias
        $album->load(['artist', 'songs']);
        
        return view('albums.edit', compact('album'));
    }

    /**
     * Actualizar un álbum (solo admin)
     */
    public function update(Request $request, Album $album)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            abort(403, 'No tienes permiso para editar álbumes.');
        }
        
        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'artist_id' => 'required|exists:artistas,id',
            'genre_id' => 'required|exists:generos,id',
            'release_date' => 'required|date',
            'cover_image' => 'nullable|image|max:2048',
            'temp_cover_path' => 'nullable|string',
            'songs' => 'nullable|array',
            'songs.*.id' => 'nullable|exists:canciones,id',
            'songs.*.title' => 'required|string|max:255',
            'songs.*.duration' => 'required|string|max:10',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Usamos una transacción de base de datos para garantizar la integridad
        DB::beginTransaction();
        
        try {
            $albumData = [
                'title' => $request->title,
                'artist_id' => $request->artist_id,
                'genre_id' => $request->genre_id,
                'release_date' => $request->release_date,
            ];
            
            // Procesamiento de imagen
            if ($request->has('temp_cover_path') && !empty($request->temp_cover_path)) {
                // Si se subió previamente una imagen temporal, usarla
                $albumData['cover_image'] = $request->temp_cover_path;
            } elseif ($request->hasFile('cover_image')) {
                // Si se subió una imagen directamente
                $albumData['cover_image'] = $request->file('cover_image')->store('covers', 'public');
            }
            
            $album->update($albumData);
            
            // Procesar canciones si se enviaron
            if ($request->has('songs') && is_array($request->songs)) {
                $existingSongIds = [];
                
                foreach ($request->songs as $songData) {
                    if (!empty($songData['title']) && !empty($songData['duration'])) {
                        // Si tiene ID es una canción existente que se actualiza
                        if (!empty($songData['id'])) {
                            $song = \App\Models\Song::find($songData['id']);
                            
                            if ($song && $song->album_id == $album->id) {
                                $song->title = $songData['title'];
                                $song->duration = $songData['duration'];
                                $song->save();
                                
                                $existingSongIds[] = $song->id;
                            }
                        } else {
                            // Es una nueva canción para este álbum
                            $song = new \App\Models\Song([
                                'title' => $songData['title'],
                                'duration' => $songData['duration'],
                                'genre_id' => $request->genre_id, // Usar el mismo género del álbum
                                'cover_image' => 'songs/default.jpg', // Imagen por defecto
                                'play_count' => 0
                            ]);
                            
                            $song->album_id = $album->id;
                            $song->artist_id = $request->artist_id; // Usar el mismo artista del álbum
                            $song->save();
                            
                            $existingSongIds[] = $song->id;
                        }
                    }
                }
                
                // Opcionalmente: eliminar canciones que ya no están en la lista enviada
                // Descomenta estas líneas si quieres que se eliminen las canciones que no se enviaron
                /*
                if (!empty($existingSongIds)) {
                    \App\Models\Song::where('album_id', $album->id)
                        ->whereNotIn('id', $existingSongIds)
                        ->delete();
                }
                */
            }
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Álbum actualizado correctamente',
                    'redirect' => route('admin.albums')
                ]);
            }
            
            return redirect()->route('admin.albums')
                            ->with('success', 'Álbum actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el álbum: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al actualizar el álbum: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un álbum (solo admin)
     */
    public function destroy(Album $album)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            if (request()->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            abort(403, 'No tienes permiso para eliminar álbumes.');
        }
        
        // Usar transacción para eliminar el álbum y actualizar relaciones
        // Desactivamos auto-commit
        DB::beginTransaction();
        try {
            // Desvincular todas las canciones de este álbum
            $album->songs()->update(['album_id' => null]);
            
            // Eliminar el álbum
            $album->delete();
            
            // Commit de la transacción
            DB::commit();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Álbum eliminado correctamente'
                ]);
            }
            
            return redirect()->route('admin.albums')
                            ->with('success', 'Álbum eliminado correctamente.');
        } catch (\Exception $e) {
            // Revertimos la transacción en caso de error
            DB::rollback();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el álbum: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.albums')
                ->with('error', 'Error al eliminar el álbum: ' . $e->getMessage());
        }
    }
    
    /**
     * Subir imagen de portada mediante AJAX
     */
    public function uploadCover(Request $request)
    {
        // Verificar si el usuario es admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'error' => 'No tienes permiso para subir imágenes.'], 403);
        }
        
        // Validar archivo
        $validator = Validator::make($request->all(), [
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        try {
            // Procesar imagen
            $file = $request->file('cover_image');
            $path = $file->store('temp/albums', 'public');
            $url = asset('storage/' . $path);
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen subida correctamente.',
                'path' => $path,
                'url' => $url
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al subir la imagen: ' . $e->getMessage()
            ]);
        }
    }
}
