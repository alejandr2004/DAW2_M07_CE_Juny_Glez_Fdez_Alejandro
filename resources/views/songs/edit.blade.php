@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-6">Editar Canción: {{ $song->title }}</h1>

    <div class="bg-white p-6 rounded-lg shadow">
        <form id="songEditForm" action="{{ route('songs.update', $song) }}" method="POST" enctype="multipart/form-data" data-song-id="{{ $song->id }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $song->title) }}" 
                        class="form-control @error('title') is-invalid @enderror" required>
                    @error('title')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="artist" class="block text-sm font-medium text-gray-700 mb-1">Artista *</label>
                    <input type="text" name="artist" id="artist" value="{{ old('artist', $song->artist) }}" 
                        class="form-control @error('artist') is-invalid @enderror" required>
                    @error('artist')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="genre_id" class="block text-sm font-medium text-gray-700 mb-1">Género *</label>
                    <select name="genre_id" id="genre_id" class="form-control @error('genre_id') is-invalid @enderror" required>
                        <option value="">Seleccionar género</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}" {{ old('genre_id', $song->genre_id) == $genre->id ? 'selected' : '' }}>
                                {{ $genre->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('genre_id')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duración (mm:ss) *</label>
                    <input type="text" name="duration" id="duration" value="{{ old('duration', $song->duration) }}" 
                        class="form-control @error('duration') is-invalid @enderror" 
                        placeholder="3:45" required>
                    @error('duration')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de lanzamiento *</label>
                    <input type="date" name="release_date" id="release_date" value="{{ old('release_date', $song->release_date) }}" 
                        class="form-control @error('release_date') is-invalid @enderror" required>
                    @error('release_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1">Imagen de portada</label>
                    @if($song->cover_image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $song->cover_image) }}" alt="{{ $song->title }}" 
                                class="w-32 h-32 object-cover rounded">
                        </div>
                    @endif
                    <input type="file" name="cover_image" id="cover_image" 
                        class="form-control @error('cover_image') is-invalid @enderror">
                    <p class="text-sm text-gray-500 mt-1">Deja en blanco para mantener la imagen actual</p>
                    @error('cover_image')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                

            </div>
            
            <div class="flex justify-between">
                <a href="{{ route('songs.show', $song) }}" class="btn-secondary py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded">Cancelar</a>
                <button type="submit" id="saveButton" class="btn-spotify py-2 px-6 bg-green-500 hover:bg-green-600 text-white rounded"><span>Actualizar Canción</span></button>
            </div>
        </form>
        
        <!-- Información adicional -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center text-sm text-gray-600">
                <span class="font-medium">Artista:</span>
                <span class="song-artist ml-2">{{ $song->artist->nombre }}</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center text-sm text-gray-600 mt-1">
                <span class="font-medium">Género:</span>
                <span class="song-genre ml-2">{{ $song->genre->name }}</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/song-edit-ajax.js') }}"></script>
@endpush

@endsection
