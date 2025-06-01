@extends('layouts.app')

@section('title', 'Detalles de Playlist')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Playlist: {{ $playlist->name ?? 'Nombre de playlist' }}</h1>
            <div>
                <a href="{{ route('playlists.index') }}" class="text-spotify hover:underline">Volver a mis playlists</a>
            </div>
        </div>
        
        <div class="mb-6">
            <p class="text-gray-700">
                <span class="px-2 py-1 rounded text-xs {{ isset($playlist) && $playlist->is_public ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ isset($playlist) && $playlist->is_public ? 'Pública' : 'Privada' }}
                </span>
                <span class="ml-4 text-gray-500">Creada por: {{ isset($playlist) ? $playlist->user->name : auth()->user()->name }}</span>
            </p>
        </div>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">Canciones</h2>
            
            @if(isset($playlist) && $playlist->songs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left">#</th>
                                <th class="py-2 px-4 text-left">Título</th>
                                <th class="py-2 px-4 text-left">Artista</th>
                                <th class="py-2 px-4 text-left">Álbum</th>
                                <th class="py-2 px-4 text-left">Duración</th>
                                <th class="py-2 px-4 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($playlist->songs as $index => $song)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4">{{ $index + 1 }}</td>
                                <td class="py-2 px-4">{{ $song->title }}</td>
                                <td class="py-2 px-4">{{ $song->artist->name }}</td>
                                <td class="py-2 px-4">{{ $song->album->name }}</td>
                                <td class="py-2 px-4">{{ $song->duration }}</td>
                                <td class="py-2 px-4">
                                    <button class="text-red-500 hover:text-red-700">Eliminar</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-gray-100 p-6 rounded-lg text-center">
                    <p class="text-gray-600 mb-4">No hay canciones en esta playlist.</p>
                    <a href="#" class="btn-spotify py-2 px-4 rounded inline-block">Añadir canciones</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
