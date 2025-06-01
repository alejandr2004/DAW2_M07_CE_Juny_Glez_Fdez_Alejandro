<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Song;
use App\Models\Album;
use App\Models\Genre;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        
        // Estadísticas para el dashboard
        $stats = [
            'songs' => Song::count(),
            'artists' => Artist::count(),
            'albums' => Album::count(),
            'genres' => Genre::count(),
            'users' => User::count(),
            'playlists' => Playlist::count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
    
    /**
     * Gestión de usuarios
     */
    public function users(Request $request)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para acceder al panel de administración.');
        }
        
        $query = User::query();
        
        // Filtrar por rol
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }
        
        // Búsqueda por nombre o email
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Ordenar
        $query->orderBy('name');
        
        // Paginar resultados
        $users = $query->paginate(15)->withQueryString();
        
        // Si es petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.user-list', compact('users'))->render(),
                'pagination' => view('partials.pagination', ['paginator' => $users])->render(),
            ]);
        }
        
        return view('admin.users.index', compact('users'));
    }
    
    /**
     * Cambiar rol de usuario
     */
    public function updateUserRole(Request $request, User $user)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para esta acción.');
        }
        
        // Validar rol
        $request->validate([
            'role' => ['required', Rule::in(['user', 'admin'])],
        ]);
        
        // No permitir cambiar el propio rol para evitar quedarse sin acceso
        if ($user->id === $request->user()->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No puedes cambiar tu propio rol'
                ]);
            }
            return back()->with('error', 'No puedes cambiar tu propio rol');
        }
        
        // Actualizar rol
        $user->role = $request->role;
        $user->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Rol de usuario actualizado correctamente',
                'user' => $user
            ]);
        }
        
        return back()->with('success', 'Rol de usuario actualizado correctamente');
    }
    
    /**
     * Eliminar usuario
     */
    public function destroyUser(Request $request, User $user)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para esta acción.');
        }
        
        // No permitir eliminar el propio usuario
        if ($user->id === $request->user()->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No puedes eliminar tu propia cuenta'
                ]);
            }
            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }
        
        // Iniciar transacción para mantener integridad de datos
        DB::beginTransaction();
        
        try {
            // Eliminar playlists del usuario
            foreach ($user->playlists as $playlist) {
                $playlist->songs()->detach(); // Desvincular canciones
                $playlist->delete();
            }
            
            // Eliminar el usuario
            $user->delete();
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario eliminado correctamente',
                    'id' => $user->id
                ]);
            }
            
            return redirect()->route('admin.users')->with('success', 'Usuario eliminado correctamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al eliminar el usuario: ' . $e->getMessage()
                ]);
            }
            
            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
    
    /**
     * Gestión de canciones
     */
    public function songs(Request $request)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para acceder al panel de administración.');
        }
        
        $query = Song::with(['album', 'genre']);
        
        // Filtrar por artista
        if ($request->has('artist') && !empty($request->artist)) {
            $query->where('artist', 'like', "%{$request->artist}%");
        }
        
        // Filtrar por género
        if ($request->has('genre_id') && !empty($request->genre_id)) {
            $query->where('genre_id', $request->genre_id);
        }
        
        // Filtrar por álbum
        if ($request->has('album_id') && !empty($request->album_id)) {
            $query->where('album_id', $request->album_id);
        }
        
        // Búsqueda por título
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        }
        
        // Ordenar
        $query->orderBy('title');
        
        // Paginar resultados
        $songs = $query->paginate(15)->withQueryString();
        
        // Obtener listas para filtros
        $genres = Genre::orderBy('name')->get();
        
        // Si es petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.song-list', compact('songs'))->render(),
                'pagination' => view('admin.partials.ajax-pagination', ['paginator' => $songs])->render(),
            ]);
        }
        
        return view('admin.songs.index', compact('songs', 'genres'));
    }
    
    /**
     * Gestión de álbumes
     */
    public function albums(Request $request)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para acceder al panel de administración.');
        }
        
        $query = Album::withCount('songs');
        
        // Filtrar por artista
        if ($request->has('artist') && !empty($request->artist)) {
            $query->where('artist', 'like', "%{$request->artist}%");
        }
        
        // Búsqueda por título
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        }
        
        // Ordenar
        $query->orderBy('title');
        
        // Paginar resultados
        $albums = $query->paginate(12)->withQueryString();
        
        // Si es petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.album-list', compact('albums'))->render(),
                'pagination' => view('admin.partials.ajax-pagination', ['paginator' => $albums])->render(),
            ]);
        }
        
        return view('admin.albums.index', compact('albums'));
    }
    
    /**
     * Gestión de géneros
     */
    public function genres(Request $request)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para acceder al panel de administración.');
        }
        
        $query = Genre::query();
        
        // Búsqueda por nombre
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Ordenar
        $query->orderBy('name');
        
        // Paginar resultados
        $genres = $query->paginate(15)->withQueryString();
        
        // Si es petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.genre-list', compact('genres'))->render(),
                'pagination' => view('admin.partials.ajax-pagination', ['paginator' => $genres])->render(),
            ]);
        }
        
        return view('admin.genres.index', compact('genres'));
    }
    
    /**
     * Almacenar nuevo género
     */
    public function storeGenre(Request $request)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para esta acción.');
        }
        
        // Validar datos
        $request->validate([
            'name' => 'required|string|max:50|unique:genres,name',
            'description' => 'nullable|string|max:255',
        ]);
        
        // Crear género
        $genre = Genre::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Género creado correctamente',
                'genre' => $genre,
                'html' => view('admin.partials.genre-item', compact('genre'))->render(),
            ]);
        }
        
        return redirect()->route('admin.genres')->with('success', 'Género creado correctamente');
    }
    
    /**
     * Actualizar género
     */
    public function updateGenre(Request $request, Genre $genre)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para esta acción.');
        }
        
        // Validar datos
        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('genres')->ignore($genre->id)],
            'description' => 'nullable|string|max:255',
        ]);
        
        // Actualizar género
        $genre->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Género actualizado correctamente',
                'genre' => $genre,
                'html' => view('admin.partials.genre-item', compact('genre'))->render(),
            ]);
        }
        
        return redirect()->route('admin.genres')->with('success', 'Género actualizado correctamente');
    }
    
    /**
     * Eliminar género
     */
    public function destroyGenre(Request $request, Genre $genre)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para esta acción.');
        }
        
        // Comprobar si hay canciones asociadas
        if ($genre->songs()->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se puede eliminar este género porque tiene canciones asociadas'
                ]);
            }
            return back()->with('error', 'No se puede eliminar este género porque tiene canciones asociadas');
        }
        
        // Eliminar género
        $genre->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Género eliminado correctamente',
                'id' => $genre->id
            ]);
        }
        
        return redirect()->route('admin.genres')->with('success', 'Género eliminado correctamente');
    }
}
