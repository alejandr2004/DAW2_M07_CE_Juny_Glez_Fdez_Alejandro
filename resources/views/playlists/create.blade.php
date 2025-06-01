@extends('layouts.app')

@section('title', 'Crear Playlist')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <h1 class="text-3xl font-bold mb-6">Crear nueva playlist</h1>
        
        <form action="#" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nombre de la playlist</label>
                <input id="name" type="text" name="name" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Visibilidad</label>
                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio" name="is_public" value="1" checked>
                        <span class="ml-2">Pública</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input type="radio" class="form-radio" name="is_public" value="0">
                        <span class="ml-2">Privada</span>
                    </label>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="btn-spotify py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear playlist
                </button>
                <a href="{{ route('playlists.index') }}" class="inline-block align-baseline font-bold text-sm text-spotify hover:text-green-600">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
