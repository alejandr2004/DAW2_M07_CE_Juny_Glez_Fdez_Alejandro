@extends('layouts.app')

@section('title', 'Playlists')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Playlists</h1>
            <a href="{{ route('playlists.create') }}" class="btn-spotify py-2 px-4 rounded">Crear Playlist</a>
        </div>
        
        <!-- MIS PLAYLISTS PRIVADAS -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Mis Playlists Privadas</h2>
            
            @if($privatePlaylistsUser->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($privatePlaylistsUser as $playlist)
                        <div class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <h3 class="text-xl font-semibold text-spotify">{{ $playlist->name }}</h3>
                            <div class="flex items-center mt-1">
                                <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">Privada</span>
                            </div>
                            <p class="text-gray-500 mt-2">{{ $playlist->songs->count() }} canciones</p>
                            <a href="{{ route('playlists.show', $playlist) }}" class="text-spotify hover:underline mt-2 inline-block">Ver playlist</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-100 p-4 rounded-lg">
                    <p class="text-gray-600">No tienes playlists privadas.</p>
                </div>
            @endif
        </div>
        
        <!-- MIS PLAYLISTS PÚBLICAS -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Mis Playlists Públicas</h2>
            
            @if($publicPlaylistsUser->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($publicPlaylistsUser as $playlist)
                        <div class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <h3 class="text-xl font-semibold text-spotify">{{ $playlist->name }}</h3>
                            <div class="flex items-center mt-1">
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Pública</span>
                            </div>
                            <p class="text-gray-500 mt-2">{{ $playlist->songs->count() }} canciones</p>
                            <a href="{{ route('playlists.show', $playlist) }}" class="text-spotify hover:underline mt-2 inline-block">Ver playlist</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-100 p-4 rounded-lg">
                    <p class="text-gray-600">No tienes playlists públicas.</p>
                </div>
            @endif
        </div>
        
        <!-- PLAYLISTS PÚBLICAS DE OTROS USUARIOS -->
        <div>
            <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Playlists Públicas de la Comunidad</h2>
            
            @if($publicPlaylistsOthers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($publicPlaylistsOthers as $playlist)
                        <div class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <h3 class="text-xl font-semibold text-spotify">{{ $playlist->name }}</h3>
                            <div class="flex items-center mt-1">
                                <span class="text-gray-600 text-sm">Creada por <span class="font-medium">{{ $playlist->user->name }}</span></span>
                            </div>
                            <p class="text-gray-500 mt-2">{{ $playlist->songs->count() }} canciones</p>
                            <a href="{{ route('playlists.show', $playlist) }}" class="text-spotify hover:underline mt-2 inline-block">Ver playlist</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-100 p-4 rounded-lg">
                    <p class="text-gray-600">No hay playlists públicas de otros usuarios disponibles.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
