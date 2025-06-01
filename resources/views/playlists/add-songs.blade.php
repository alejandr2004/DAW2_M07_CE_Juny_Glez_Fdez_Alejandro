@extends('layouts.app')

@section('title', 'Añadir canciones a "'.$playlist->name.'"')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Añadir canciones a la playlist</h1>
                <a href="{{ route('playlists.show', $playlist) }}" class="text-spotify hover:underline">
                    &larr; Volver a "{{ $playlist->name }}"
                </a>
            </div>
            <p class="text-gray-600 mt-2">Selecciona las canciones que quieres añadir a tu playlist. Las canciones que ya están en tu playlist aparecen marcadas.</p>
        </div>
        
        <form method="POST" action="{{ route('playlists.store-songs', $playlist) }}" class="mb-6">
            @csrf
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($songs as $song)
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 {{ in_array($song->id, $playlistSongIds) ? 'border-green-500' : 'border-spotify' }}">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <input class="h-5 w-5 text-spotify rounded focus:ring-spotify" 
                                        type="checkbox" 
                                        name="songs[]" 
                                        value="{{ $song->id }}" 
                                        id="song-{{ $song->id }}"
                                        {{ in_array($song->id, $playlistSongIds) ? 'checked disabled' : '' }}>
                                </div>
                                <label class="ml-3 flex-1 cursor-pointer" for="song-{{ $song->id }}">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 truncate">{{ $song->title }}</h3>
                                        <div class="flex justify-between items-center mt-1">
                                            <div class="text-sm text-gray-600">{{ $song->artist->name }}</div>
                                            <div class="text-xs text-gray-500 ml-2">{{ $song->duration }}</div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $song->album->title }}</div>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $song->genre->name }}
                                            </span>
                                            @if(in_array($song->id, $playlistSongIds))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 ml-2">
                                                    Ya añadida
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($songs->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        No hay canciones disponibles para añadir.
                    </div>
                @endif
            </div>
            
            <div class="mb-6">
                {{ $songs->links() }}
            </div>
            
            @error('songs')
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ $message }}</p>
                </div>
            @enderror
            
            <div class="flex justify-between items-center">
                <a href="{{ route('playlists.show', $playlist) }}" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-spotify">
                    Cancelar
                </a>
                <button type="submit" class="btn-spotify py-2 px-6 rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-spotify">
                    Añadir canciones seleccionadas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
