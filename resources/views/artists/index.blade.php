@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-6">Artistas</h1>

    <!-- Filtro de búsqueda -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form action="{{ route('artists.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-grow">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar artista</label>
                <input type="text" name="search" id="search" class="form-control" 
                    value="{{ request('search') }}" placeholder="Nombre del artista">
            </div>
            
            <div class="self-end">
                <button type="submit" class="btn-spotify py-2 px-4 w-full">Buscar</button>
            </div>
        </form>
    </div>

    <!-- Lista de artistas -->
    @if($artists->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($artists as $artist)
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative pb-[100%] bg-gray-200 overflow-hidden">
                        @if($artist->imagen)
                            <img src="{{ asset('storage/' . $artist->imagen) }}" alt="{{ $artist->nombre }}" 
                                class="absolute w-full h-full object-cover">
                        @else
                            @php
                                // Crear colores pseudo-aleatorios basados en el ID del artista
                                $artistId = $artist->id;
                                $hue = ($artistId * 107) % 360;
                                $colorPrimary = "hsla({$hue}, 65%, 45%, 0.9)";
                                $colorSecondary = "hsla({$hue}, 75%, 25%, 0.8)";
                            @endphp
                            
                            <div class="absolute inset-0" style="background: linear-gradient(135deg, {{$colorPrimary}}, {{$colorSecondary}});">
                                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(30deg, rgba(255,255,255,0.15) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.15) 87.5%, rgba(255,255,255,0.15)), linear-gradient(150deg, rgba(255,255,255,0.15) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.15) 87.5%, rgba(255,255,255,0.15)); background-size: 40px 70px;"></div>
                                
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center text-white">
                                        <div class="rounded-full bg-white bg-opacity-20 p-2 mx-auto">
                                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M18 10.849c0 .966-.784 1.75-1.75 1.75h-1v1a.75.75 0 01-1.5 0v-1h-1.75a1.75 1.75 0 110-3.5H13v-1h-.75a.75.75 0 110-1.5H13v-1h.25a1.75 1.75 0 110-3.5H12v-.5a.75.75 0 00-1.5 0v.5H9.75a1.75 1.75 0 110 3.5H10v1h.25a.75.75 0 110 1.5H10v1h-.75a1.75 1.75 0 110 3.5H10v1a.75.75 0 001.5 0v-1h.75c.966 0 1.75-.784 1.75-1.75v-.25h1v.25c0 .966.784 1.75 1.75 1.75h.25a.75.75 0 000-1.5H16.5v-1.75c0-.966-.784-1.75-1.75-1.75h-.25v-.25c0-.966-.784-1.75-1.75-1.75h-.25v.25c0 .966.784 1.75 1.75 1.75h.25v1.75c0 .138.112.25.25.25h1.75a.25.25 0 00.25-.25z" />
                                            </svg>
                                        </div>
                                        <div class="mt-2 text-xl font-bold">{{ strtoupper(substr($artist->nombre, 0, 1)) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-2">{{ $artist->nombre }}</h3>
                        <p class="text-gray-600 mb-2 truncate">{{ $artist->songs_count ?? 0 }} canciones</p>
                        <a href="{{ route('artists.show', $artist) }}" class="text-spotify hover:underline">Ver artista</a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-8">
            {{ $artists->links() }}
        </div>
    @else
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <p class="text-lg text-gray-600">No se encontraron artistas.</p>
            @if(request('search'))
                <a href="{{ route('artists.index') }}" class="btn-spotify inline-block mt-4 py-2 px-4">Ver todos los artistas</a>
            @endif
        </div>
    @endif

    @auth
        @if(auth()->user()->role === 'admin')
            <div class="mt-8">
                <a href="{{ route('artists.create') }}" class="btn-spotify py-2 px-4 rounded inline-block">Añadir nuevo artista</a>
            </div>
        @endif
    @endauth
</div>
@endsection
