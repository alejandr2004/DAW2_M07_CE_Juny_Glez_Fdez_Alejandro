<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Song;
use App\Models\Album;
use App\Models\Genre;
use App\Models\Playlist;
use App\Models\Artist;
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
            'albums' => Album::count(),
            'genres' => Genre::count(),
            'users' => User::count(),
            'playlists' => Playlist::count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
    
    /**
     * Gestión de usuarios
     * @method GET|POST
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
        
        // Ordenar resultados
        $sortField = 'name'; // Ordenación por defecto
        $sortDirection = 'asc';
        
        if ($request->has('sort') && !empty($request->sort)) {
            switch ($request->sort) {
                case 'name':
                    $sortField = 'name';
                    break;
                case 'email':
                    $sortField = 'email';
                    break;
                case 'created_at':
                    $sortField = 'created_at';
                    $sortDirection = 'desc'; // Más recientes primero
                    break;
            }
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Paginar resultados
        $perPage = $request->per_page ?? 15;
        $users = $query->paginate($perPage)->withQueryString();
        
        // Si es petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.user-list', compact('users'))->render(),
                'pagination' => view('admin.partials.ajax-pagination', ['paginator' => $users])->render(),
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
     * Deshabilitar o habilitar un usuario
     */
    public function toggleDisabled(Request $request, User $user)
    {
        // Verificar si el usuario es admin
        if ($request->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect()->route('home')->with('error', 'No tienes permisos para esta acción.');
        }
        
        // No permitir deshabilitar la propia cuenta
        if ($user->id === $request->user()->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No puedes deshabilitar tu propia cuenta'
                ]);
            }
            return back()->with('error', 'No puedes deshabilitar tu propia cuenta');
        }
        
        // Cambiar estado de deshabilitado
        $user->is_disabled = !$user->is_disabled;
        $user->save();
        
        $message = $user->is_disabled ? 'Usuario deshabilitado correctamente' : 'Usuario habilitado correctamente';
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => $user
            ]);
        }
        
        return back()->with('success', $message);
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
                    'message' => 'Error al eliminar el usuario: ' . $e->getMessage(),
                    'error' => $e->getMessage()
                ], 500);
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
        
        // Log para depurar la petición
        \Log::info('AdminController@songs - Petición recibida', [
            'esAjax' => $request->ajax(),
            'method' => $request->method(),
            'parametros' => $request->all()
        ]);
        
        $query = Song::with(['album', 'genre', 'artist']);
        
        // Filtrar por artista (usando el campo 'nombre' en español)
        if ($request->has('artist') && !empty($request->artist)) {
            $artistName = $request->artist;
            \Log::info('Filtrando por artista', ['nombre' => $artistName]);
            
            $query->whereHas('artist', function($q) use ($artistName) {
                $q->where('nombre', 'like', "%{$artistName}%");
            });
        }
        
        // Filtrar por género
        if ($request->has('genre_id') && !empty($request->genre_id)) {
            \Log::info('Filtrando por género', ['id' => $request->genre_id]);
            $query->where('genre_id', $request->genre_id);
        }
        
        // Filtrar por álbum
        if ($request->has('album_id') && !empty($request->album_id)) {
            \Log::info('Filtrando por álbum', ['id' => $request->album_id]);
            $query->where('album_id', $request->album_id);
        }
        
        // Búsqueda por título
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            \Log::info('Buscando por título', ['search' => $search]);
            
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        
        // Ordenar resultados
        $query->orderBy('title');
        
        // Paginar resultados
        $songs = $query->paginate(15)->withQueryString();
        
        // Obtener listas para filtros
        $genres = Genre::orderBy('name')->get();
        $artists = Artist::orderBy('nombre')->get(); // Usar 'nombre' en español, no 'name'
        $albums = Album::orderBy('title')->get();
        
        // Si es petición AJAX, devolver vista parcial
        if ($request->ajax()) {
            \Log::info('Devolviendo respuesta AJAX', [
                'canciones' => $songs->count(),
                'paginación' => $songs->lastPage() > 1 ? 'Sí' : 'No'
            ]);
            
            return response()->json([
                'html' => view('admin.partials.song-list', compact('songs', 'genres', 'artists', 'albums'))->render(),
                'pagination' => view('admin.partials.ajax-pagination', ['paginator' => $songs])->render(),
            ]);
        }
        
        return view('admin.songs.index', compact('songs', 'genres', 'artists', 'albums'));
    }
    
    /**
     * Gestión de álbumes
     * @method GET|POST
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

        // Registrar lo que estamos recibiendo en el formulario
        \Log::info('Request datos para filtrado de albums:', $request->all());
        
        $query = Album::with('artist')->withCount('songs');
        
        // Filtrar por artista
        if ($request->has('artist') && !empty($request->artist)) {
            $query->whereHas('artist', function($q) use ($request) {
                // Usando el campo en español 'nombre' para la búsqueda
                $q->where('nombre', 'like', "%{$request->artist}%");
            });
        }
        
        // Búsqueda por título o artista
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('artist', function($q) use ($search) {
                      // Usando el campo en español 'nombre' para la búsqueda
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }
        
        // Ordenar resultados
        $sortField = 'title'; // Ordenación por defecto
        $sortDirection = 'asc';
        
        if ($request->has('sort') && !empty($request->sort)) {
            switch ($request->sort) {
                case 'title':
                    $sortField = 'title';
                    break;
                case 'artist':
                    // No podemos ordenar directamente por el nombre del artista aquí,
                    // lo manejaremos usando orderBy en raw SQL
                    $query->select('albums.*')
                        ->leftJoin('artists', 'albums.artist_id', '=', 'artists.id')
                        ->orderBy('artists.nombre', 'asc');
                    $sortField = null; // Ya hemos aplicado la ordenación
                    break;
                case 'release_date':
                    $sortField = 'release_date';
                    $sortDirection = 'desc'; // Más recientes primero
                    break;
                case 'songs_count':
                    $query->orderBy('songs_count', 'desc');
                    $sortField = null; // Ya hemos aplicado la ordenación
                    break;
            }
        }
        
        // Aplicar ordenación si no se ha aplicado ya
        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }
        
        // Paginar resultados
        $albums = $query->paginate(12)->withQueryString();
        
        // Si es petición AJAX, devolver vista parcial
        if ($request->ajax() || $request->isMethod('post')) {
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
                'redirect' => route('admin.genres'),
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
        
        // Usar transacción para eliminar el género
        try {
            DB::beginTransaction();
            
            // Si el género tiene canciones, asignarlas a un género por defecto o eliminarlas
            if ($genre->songs()->count() > 0) {
                // Aquí podrías crear una lógica para reasignar canciones a otro género
                // o eliminarlas si así lo prefieres. En este caso las eliminaremos.
                $genre->songs()->delete();
            }
            
            // Eliminar género
            $genre->delete();
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Género y sus canciones asociadas eliminados correctamente',
                    'id' => $genre->id,
                    'redirect' => route('admin.genres')
                ]);
            }
            
            return redirect()->route('admin.genres')->with('success', 'Género y sus canciones asociadas eliminados correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ha ocurrido un error al eliminar el género: ' . $e->getMessage()
                ]);
            }
            
            return back()->with('error', 'Ha ocurrido un error al eliminar el género: ' . $e->getMessage());
        }
    }
}
