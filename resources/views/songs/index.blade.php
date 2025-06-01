@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-6">Explora Canciones</h1>

    <!-- Filtros de búsqueda -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form action="{{ route('songs.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                <!-- Búsqueda por título o artista -->
                <div class="md:col-span-4">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search" id="search" class="form-control w-full" 
                        value="{{ request('search') }}" placeholder="Título o artista">
                </div>
                
                <!-- Selección de artista -->
                <div class="md:col-span-4">
                    <label for="artist" class="block text-sm font-medium text-gray-700 mb-1">Artista</label>
                    <select name="artist" id="artist" class="form-control w-full">
                        <option value="">Todos los artistas</option>
                        @foreach($artists as $artist)
                            <option value="{{ $artist->id }}" {{ request('artist') == $artist->id ? 'selected' : '' }}>
                                {{ $artist->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Botón de filtrar -->
                <div class="md:col-span-4 self-end">
                    <button type="submit" class="btn-spotify py-2 px-4 w-full">Aplicar filtros</button>
                </div>
            </div>
            
            <!-- Filtros de género múltiples y sumativos -->
            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Géneros (selección múltiple)</label>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    @foreach($genres as $genre)
                        <div class="flex items-center">
                            <input type="checkbox" name="genres[]" id="genre-{{ $genre->id }}" value="{{ $genre->id }}" 
                                {{ in_array($genre->id, $selectedGenres) ? 'checked' : '' }} class="mr-2">
                            <label for="genre-{{ $genre->id }}" class="text-sm">{{ $genre->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <!-- Lista de canciones -->
    @if($songs->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($songs as $song)
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative pb-[100%] bg-gray-200 overflow-hidden">
                        @php
                            // Crear colores pseudo-aleatorios basados en el ID de la canción
                            $songId = $song->id;
                            $hue = ($songId * 137) % 360; // Distribución uniforme de colores en el espectro
                            $colorPrimary = "hsla({$hue}, 80%, 40%, 0.9)";
                            $colorSecondary = "hsla({$hue}, 70%, 30%, 0.8)";
                            $patternType = $songId % 5; // 5 patrones diferentes
                        @endphp
                        
                        <div class="absolute inset-0" style="background: linear-gradient(135deg, {{$colorPrimary}}, {{$colorSecondary}});">
                            @if($patternType == 0)
                                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.3) 2px, transparent 2px), radial-gradient(circle at 80% 40%, rgba(255,255,255,0.3) 2px, transparent 2px), radial-gradient(circle at 40% 70%, rgba(255,255,255,0.3) 2px, transparent 2px); background-size: 30px 30px;"></div>
                            @elseif($patternType == 1)
                                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(45deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent); background-size: 20px 20px;"></div>
                            @elseif($patternType == 2)
                                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(to right, rgba(255,255,255,0.2) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 20px 20px;"></div>
                            @elseif($patternType == 3)
                                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle, rgba(255,255,255,0.3) 20%, transparent 20%), radial-gradient(circle, rgba(255,255,255,0.3) 20%, transparent 20%); background-size: 30px 30px; background-position: 0 0, 15px 15px;"></div>
                            @else
                                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(135deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent); background-size: 20px 20px;"></div>
                            @endif
                            
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center text-white w-full h-full flex flex-col items-center justify-center">
                                    <div class="rounded-full bg-white bg-opacity-20 p-3">
                                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1 truncate">{{ $song->title }}</h3>
                        <p class="text-gray-600 mb-2 truncate">{{ $song->artist->name }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ $song->duration }}</span>
                            <a href="{{ route('songs.show', $song) }}" class="text-spotify hover:underline">Ver detalles</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-8">
            {{ $songs->links() }}
        </div>
    @else
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <p class="text-lg text-gray-600">No se encontraron canciones con los filtros seleccionados.</p>
            @if(request('search') || request()->has('genres') || request('artist'))
                <a href="{{ route('songs.index') }}" class="btn-spotify inline-block mt-4 py-2 px-4">Ver todas las canciones</a>
            @endif
        </div>
    @endif

    @auth
        @if(auth()->user()->role === 'admin')
            <div class="mt-8">
                <a href="{{ route('songs.create') }}" class="btn-spotify py-2 px-4 rounded inline-block">Añadir nueva canción</a>
            </div>
        @endif
    @endauth
</div>
@endsection
