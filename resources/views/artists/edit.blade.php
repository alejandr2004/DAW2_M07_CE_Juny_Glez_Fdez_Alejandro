@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-6">Editar Artista: {{ $artist->nombre }}</h1>

    <div class="bg-white p-6 rounded-lg shadow">
        <form action="{{ route('artists.update', $artist) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $artist->nombre) }}" 
                        class="form-control @error('nombre') is-invalid @enderror" required>
                    @error('nombre')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="pais" class="block text-sm font-medium text-gray-700 mb-1">País</label>
                    <input type="text" name="pais" id="pais" value="{{ old('pais', $artist->pais) }}" 
                        class="form-control @error('pais') is-invalid @enderror">
                    @error('pais')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="biografia" class="block text-sm font-medium text-gray-700 mb-1">Biografía</label>
                    <textarea name="biografia" id="biografia" rows="4" 
                        class="form-control @error('biografia') is-invalid @enderror">{{ old('biografia', $artist->biografia) }}</textarea>
                    @error('biografia')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label for="imagen" class="block text-sm font-medium text-gray-700 mb-1">Imagen del artista</label>
                    @if($artist->imagen)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $artist->imagen) }}" alt="{{ $artist->nombre }}" 
                                class="w-32 h-32 object-cover rounded">
                        </div>
                    @endif
                    <input type="file" name="imagen" id="imagen" 
                        class="form-control @error('imagen') is-invalid @enderror">
                    <p class="text-sm text-gray-500 mt-1">Deja en blanco para mantener la imagen actual</p>
                    @error('imagen')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="flex justify-between">
                <a href="{{ route('artists.show', $artist) }}" class="btn-secondary py-2 px-4">Cancelar</a>
                <button type="submit" class="btn-spotify py-2 px-6">Actualizar Artista</button>
            </div>
        </form>
    </div>
</div>
@endsection
