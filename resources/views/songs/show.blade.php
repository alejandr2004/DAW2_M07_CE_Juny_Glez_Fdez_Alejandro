@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('songs.index') }}" class="text-spotify hover:underline">
            &larr; Volver a canciones
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/4 mb-6 md:mb-0 md:mr-8">
                    <div class="w-full aspect-square rounded-lg overflow-hidden">
                        @php
                            // Crear colores pseudo-aleatorios basados en el ID de la canción
                            $songId = $song->id;
                            $hue = ($songId * 137) % 360;
                            $colorPrimary = "hsla({$hue}, 80%, 40%, 0.9)";
                            $colorSecondary = "hsla({$hue}, 70%, 30%, 0.8)";
                            $patternType = $songId % 5;
                        @endphp
                        
                        <div class="w-full h-full relative" style="background: linear-gradient(135deg, {{$colorPrimary}}, {{$colorSecondary}});">
                            @if($patternType == 0)
                                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.3) 2px, transparent 2px), radial-gradient(circle at 80% 40%, rgba(255,255,255,0.3) 2px, transparent 2px), radial-gradient(circle at 40% 70%, rgba(255,255,255,0.3) 2px, transparent 2px); background-size: 30px 30px;"></div>
                            @elseif($patternType == 1)
                                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: linear-gradient(45deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent); background-size: 20px 20px;"></div>
                            @elseif($patternType == 2)
                                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: linear-gradient(to right, rgba(255,255,255,0.2) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 20px 20px;"></div>
                            @elseif($patternType == 3)
                                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle, rgba(255,255,255,0.3) 20%, transparent 20%), radial-gradient(circle, rgba(255,255,255,0.3) 20%, transparent 20%); background-size: 30px 30px; background-position: 0 0, 15px 15px;"></div>
                            @else
                                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: linear-gradient(135deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent); background-size: 20px 20px;"></div>
                            @endif
                            
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center text-white">
                                    <div class="rounded-full bg-white bg-opacity-20 p-4 mx-auto">
                                        <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
                                        </svg>
                                    </div>
                                    <div class="mt-4 text-2xl font-bold">{{ strtoupper(substr($song->title, 0, 1)) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:w-3/4">
                    <h1 class="text-3xl font-bold mb-2">{{ $song->title }}</h1>
                    <div class="mb-4">
                        <a href="{{ route('artists.show', $song->artist) }}" class="text-xl text-spotify hover:underline">
                            {{ $song->artist->name }}
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-gray-600">
                                <span class="font-semibold">Género:</span> 
                                {{ $song->genre->name }}
                            </p>
                            <p class="text-gray-600">
                                <span class="font-semibold">Duración:</span> 
                                {{ $song->duration }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600">
                                <span class="font-semibold">Álbum:</span> 
                                {{ $song->album ? $song->album->title : 'No disponible' }}
                            </p>
                            <p class="text-gray-600">
                                <span class="font-semibold">Reproducciones:</span> 
                                {{ number_format($song->play_count) }}
                            </p>
                        </div>
                    </div>

                    <!-- Botón de reproducción con contador de plays -->
                    <form action="{{ route('songs.play', $song) }}" method="POST" class="mb-6">
                        @csrf
                        <button type="submit" class="btn-spotify py-2 px-6">
                            <i class="fas fa-play mr-2"></i> Reproducir
                        </button>
                    </form>

                    @auth
                        <!-- Opciones de playlist para usuarios autenticados -->
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold mb-2">Añadir a playlist</h3>
                            
                            @if(auth()->user()->playlists->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach(auth()->user()->playlists as $playlist)
                                        <div class="flex items-center">
                                            @if($playlist->songs->contains($song->id))
                                                <form action="{{ route('playlists.remove-song', [$playlist, $song]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                                        <i class="fas fa-minus-circle mr-1"></i> Quitar de {{ $playlist->name }}
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('playlists.store-songs', $playlist) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="songs[]" value="{{ $song->id }}">
                                                    <button type="submit" class="text-spotify hover:underline">
                                                        <i class="fas fa-plus-circle mr-1"></i> Añadir a {{ $playlist->name }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">
                                    No tienes playlists. <a href="{{ route('playlists.create') }}" class="text-spotify hover:underline">Crear una nueva</a>
                                </p>
                            @endif
                        </div>
                    @endauth

                    @auth
                        @if(auth()->user()->role === 'admin')
                            <div class="mt-8 pt-4 border-t border-gray-200">
                                <h3 class="text-lg font-semibold mb-2">Opciones de administrador</h3>
                                <div class="flex space-x-2">
                                    <a href="{{ route('songs.edit', $song) }}" class="btn-secondary py-1 px-3 text-sm">
                                        Editar canción
                                    </a>
                                    <form action="{{ route('songs.destroy', $song) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger py-1 px-3 text-sm" 
                                                onclick="return confirm('¿Estás seguro? Esta acción no se puede deshacer.')">
                                            Eliminar canción
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
