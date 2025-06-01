@extends('layouts.app')

@section('title', 'Mis Playlists')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Mis Playlists</h1>
            <a href="{{ route('playlists.create') }}" class="btn-spotify py-2 px-4 rounded">Crear Playlist</a>
        </div>
        
        @if(auth()->user()->playlists->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(auth()->user()->playlists as $playlist)
                    <div class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-semibold text-spotify">{{ $playlist->name }}</h3>
                        <p class="text-gray-600">{{ $playlist->is_public ? 'Pública' : 'Privada' }}</p>
                        <p class="text-gray-500">{{ $playlist->songs->count() }} canciones</p>
                        <a href="{{ route('playlists.show', $playlist) }}" class="text-spotify hover:underline mt-2 inline-block">Ver playlist</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-100 p-6 rounded-lg text-center">
                <p class="text-gray-600 mb-4">Aún no has creado ninguna playlist.</p>
                <a href="{{ route('playlists.create') }}" class="btn-spotify py-2 px-4 rounded inline-block">Crear mi primera playlist</a>
            </div>
        @endif
    </div>
</div>
@endsection
