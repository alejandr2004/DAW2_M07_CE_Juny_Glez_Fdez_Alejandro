<?php

namespace App\Http\Controllers;

use App\Models\Album;
// Artist eliminado
use Illuminate\Http\Request;
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
        
        return view('albums.create');
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
        
        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'cover_image' => 'nullable|image|max:2048',
            'temp_cover_path' => 'nullable|string',
        ]);
        
        $albumData = $request->only(['title', 'artist', 'release_year']);
        
        // Procesamiento de imagen
        if ($request->has('temp_cover_path') && !empty($request->temp_cover_path)) {
            // Si se subió previamente una imagen temporal, usarla
            $albumData['cover_image'] = $request->temp_cover_path;
        } elseif ($request->hasFile('cover_image')) {
            // Si se subió una imagen directamente
            $albumData['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }
        
        $album = Album::create($albumData);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Álbum creado correctamente',
                'redirect' => route('admin.albums')
            ]);
        }
        
        return redirect()->route('admin.albums')
                        ->with('success', 'Álbum creado correctamente.');
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
        
        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'cover_image' => 'nullable|image|max:2048',
            'temp_cover_path' => 'nullable|string',
        ]);
        
        $albumData = $request->only(['title', 'artist', 'release_year']);
        
        // Procesamiento de imagen
        if ($request->has('temp_cover_path') && !empty($request->temp_cover_path)) {
            // Si se subió previamente una imagen temporal, usarla
            $albumData['cover_image'] = $request->temp_cover_path;
        } elseif ($request->hasFile('cover_image')) {
            // Si se subió una imagen directamente
            $albumData['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }
        
        $album->update($albumData);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Álbum actualizado correctamente',
                'redirect' => route('admin.albums')
            ]);
        }
        
        return redirect()->route('admin.albums')
                        ->with('success', 'Álbum actualizado correctamente.');
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
        DB::beginTransaction();
        try {
            // Desvincular todas las canciones de este álbum
            $album->songs()->update(['album_id' => null]);
            
            // Eliminar el álbum
            $album->delete();
            
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
            DB::rollback();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el álbum: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('albums.index')
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
