@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-6">Añadir Nueva Canción</h1>

    <div class="bg-white p-6 rounded-lg shadow">
        <form action="{{ route('songs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" 
                        class="form-control @error('title') is-invalid @enderror" required>
                    @error('title')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="artist" class="block text-sm font-medium text-gray-700 mb-1">Artista *</label>
                    <input type="text" name="artist" id="artist" value="{{ old('artist') }}" 
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
                            <option value="{{ $genre->id }}" {{ old('genre_id') == $genre->id ? 'selected' : '' }}>
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
                    <input type="text" name="duration" id="duration" value="{{ old('duration') }}" 
                        class="form-control @error('duration') is-invalid @enderror" 
                        placeholder="3:45" required>
                    @error('duration')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de lanzamiento *</label>
                    <input type="date" name="release_date" id="release_date" value="{{ old('release_date') }}" 
                        class="form-control @error('release_date') is-invalid @enderror" required>
                    @error('release_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1">Imagen de portada</label>
                    <input type="file" name="cover_image" id="cover_image" 
                        class="form-control @error('cover_image') is-invalid @enderror">
                    @error('cover_image')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                

            </div>
            
            <div class="flex justify-between">
                <a href="{{ route('songs.index') }}" class="btn-secondary py-2 px-4">Cancelar</a>
                <button type="submit" class="btn-spotify py-2 px-6">Guardar Canción</button>
            </div>
        </form>
    </div>
</div>
@endsection
