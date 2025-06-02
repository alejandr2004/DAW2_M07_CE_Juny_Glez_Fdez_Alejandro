@extends('layouts.app')

@section('content')
@include('admin.partials.notification')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Canciones</h1>
        <div class="space-x-2">
            <a href="{{ route('songs.create') }}" class="bg-spotify hover:bg-spotify-dark text-white py-2 px-4 rounded">
                Nueva Canción
            </a>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                Volver al Dashboard
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
            <h2 class="text-lg font-semibold">Lista de canciones</h2>
            
            <div class="w-full md:w-auto">
                <form action="{{ route('admin.songs') }}" method="POST" class="ajax-filter-form flex flex-col md:flex-row items-start md:items-center gap-2" data-target="content-container">
                    @csrf
                    <div class="relative w-full md:w-auto">
                        <input type="text" name="search" id="search" placeholder="Buscar por título..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify pr-10">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <select name="artist" id="artist" class="w-full md:w-auto border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                        <option value="">Todos los artistas</option>
                        @foreach(App\Models\Artist::orderBy('nombre')->get() as $artist)
                            <option value="{{ $artist->nombre }}" {{ request()->get('artist') == $artist->nombre ? 'selected' : '' }}>{{ $artist->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="genre_id" id="genre_id" class="w-full md:w-auto border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                        <option value="">Todos los géneros</option>
                        @foreach(App\Models\Genre::orderBy('name')->get() as $genre)
                            <option value="{{ $genre->id }}" {{ request()->get('genre_id') == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                    <select name="sort" id="sort" class="w-full md:w-auto border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                        <option value="title" {{ request()->get('sort') == 'title' ? 'selected' : '' }}>Ordenar por título</option>
                        <option value="artist" {{ request()->get('sort') == 'artist' ? 'selected' : '' }}>Ordenar por artista</option>
                        <option value="genre" {{ request()->get('sort') == 'genre' ? 'selected' : '' }}>Ordenar por género</option>
                        <option value="duration" {{ request()->get('sort') == 'duration' ? 'selected' : '' }}>Ordenar por duración</option>
                        <option value="newest" {{ request()->get('sort') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                    </select>
                    <button type="submit" class="bg-spotify hover:bg-spotify-dark text-white py-2 px-4 rounded">
                        Filtrar
                    </button>
                    <button type="button" id="reset-filters" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded">
                        Limpiar
                    </button>
                </form>
            </div>
        </div>
        
        <div id="content-container">
            @include('admin.partials.song-list', ['songs' => $songs])
        </div>
        
        <!-- Contenedor para la paginación AJAX -->
        <div id="pagination-container">
            {{ $songs->links('admin.partials.ajax-pagination') }}
        </div>
    </div>
</div>

<!-- Incluir modal de eliminación -->
@include('admin.partials.delete-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
@endpush
