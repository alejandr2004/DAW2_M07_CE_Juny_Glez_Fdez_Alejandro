<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlaylistController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Las rutas ya están protegidas en web.php con middleware auth
    }

    /**
     * Mostrar lista de playlists del usuario autenticado
     * Separadas en playlists públicas (de otros usuarios) y privadas (del usuario actual)
     */
    public function index()
    {
        // Playlists privadas del usuario actual
        $privatePlaylistsUser = auth()->user()->playlists->where('is_public', false);
        
        // Playlists públicas del usuario actual
        $publicPlaylistsUser = auth()->user()->playlists->where('is_public', true);
        
        // Playlists públicas de otros usuarios
        $publicPlaylistsOthers = Playlist::with('user')
            ->where('is_public', true)
            ->where('user_id', '!=', auth()->id())
            ->get();
            
        return view('playlists.index', compact('privatePlaylistsUser', 'publicPlaylistsUser', 'publicPlaylistsOthers'));
    }

    /**
     * Mostrar formulario para crear una playlist
     */
    public function create()
    {
        return view('playlists.create');
    }

    /**
     * Almacenar una nueva playlist
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_public' => 'sometimes|boolean',
        ]);

        $playlist = auth()->user()->playlists()->create([
            'name' => $validated['name'],
            'is_public' => $request->has('is_public') ? true : false,
        ]);

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Playlist creada correctamente.');
    }

    /**
     * Mostrar una playlist específica
     */
    public function show(Playlist $playlist)
    {
        // Verificar si el usuario puede ver esta playlist
        if (!$playlist->is_public && $playlist->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para ver esta playlist.');
        }

        return view('playlists.show', compact('playlist'));
    }

    /**
     * Mostrar formulario para editar una playlist
     */
    public function edit(Playlist $playlist)
    {
        // Verificar si el usuario es dueño de la playlist
        if ($playlist->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar esta playlist.');
        }

        return view('playlists.edit', compact('playlist'));
    }

    /**
     * Actualizar una playlist específica
     */
    public function update(Request $request, Playlist $playlist)
    {
        // Verificar si el usuario es dueño de la playlist
        if ($playlist->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para actualizar esta playlist.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_public' => 'sometimes|boolean',
        ]);

        $playlist->update([
            'name' => $validated['name'],
            'is_public' => $request->has('is_public') ? true : false,
        ]);

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Playlist actualizada correctamente.');
    }

    /**
     * Eliminar una playlist específica
     */
    public function destroy(Playlist $playlist)
    {
        // Verificar si el usuario es dueño de la playlist
        if ($playlist->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar esta playlist.');
        }

        $playlist->delete();

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist eliminada correctamente.');
    }

    /**
     * Mostrar formulario para añadir canciones a una playlist
     */
    public function addSongs(Playlist $playlist)
    {
        // Verificar si el usuario es dueño de la playlist
        if ($playlist->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta playlist.');
        }

        // Obtener canciones disponibles con paginación (15 por página)
        $songs = Song::with(['artist', 'album', 'genre'])
            ->orderBy('title')
            ->paginate(12);
        
        // Obtener IDs de canciones ya en la playlist
        $playlistSongIds = $playlist->songs->pluck('id')->toArray();

        return view('playlists.add-songs', compact('playlist', 'songs', 'playlistSongIds'));
    }

    /**
     * Guardar canciones añadidas a una playlist
     */
    public function storeSongs(Request $request, Playlist $playlist)
    {
        // Verificar si el usuario es dueño de la playlist
        if ($playlist->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta playlist.');
        }

        $validated = $request->validate([
            'songs' => 'required|array',
            'songs.*' => 'exists:canciones,id',
        ]);

        // Determinar el orden máximo actual
        $maxOrder = $playlist->songs()->max('order') ?? 0;
        
        // Añadir nuevas canciones
        foreach ($validated['songs'] as $songId) {
            // Verificar si la canción ya está en la playlist
            if (!$playlist->songs->contains($songId)) {
                $maxOrder++;
                $playlist->songs()->attach($songId, ['order' => $maxOrder]);
            }
        }

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Canciones añadidas correctamente a la playlist.');
    }

    /**
     * Eliminar una canción de una playlist
     */
    public function removeSong(Playlist $playlist, Song $song)
    {
        // Verificar si el usuario es dueño de la playlist
        if ($playlist->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta playlist. Solo el propietario puede eliminar canciones.');
        }

        // Eliminar la canción de la playlist
        $playlist->songs()->detach($song->id);
        
        // Reordenar las canciones restantes
        $songs = $playlist->songs()->orderBy('order')->get();
        foreach ($songs as $index => $song) {
            $playlist->songs()->updateExistingPivot($song->id, ['order' => $index + 1]);
        }

        return back()->with('success', 'Canción eliminada de la playlist.');
    }
}
