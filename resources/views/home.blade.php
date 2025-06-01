@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <h1 class="text-3xl font-bold mb-6">Bienvenido a MySpotify</h1>
        
        @auth
            <p class="text-xl mb-4">Hola, {{ auth()->user()->name }}!</p>
            <div class="mt-6">
                <h2 class="text-2xl font-semibold mb-4">Tus playlists</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if(auth()->user()->playlists->count() > 0)
                        @foreach(auth()->user()->playlists as $playlist)
                            <div class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <h3 class="text-xl font-semibold text-spotify">{{ $playlist->name }}</h3>
                                <p class="text-gray-600">{{ $playlist->is_public ? 'Pública' : 'Privada' }}</p>
                                <p class="text-gray-500">{{ $playlist->songs->count() }} canciones</p>
                                <a href="{{ route('playlists.show', $playlist) }}" class="text-spotify hover:underline mt-2 inline-block">Ver playlist</a>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-600 col-span-3">Aún no has creado ninguna playlist.</p>
                    @endif
                </div>
                <div class="mt-4">
                    <a href="{{ route('playlists.create') }}" class="btn-spotify py-2 px-4 rounded inline-block">Crear nueva playlist</a>
                </div>
            </div>
        @else
            <p class="text-xl mb-6">La mejor plataforma para escuchar y compartir música.</p>
            <div class="flex space-x-4">
                <a href="{{ route('login') }}" class="btn-spotify py-2 px-6 rounded">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="bg-gray-800 text-white py-2 px-6 rounded hover:bg-gray-700">Registrarse</a>
            </div>
        @endauth
    </div>
</div>

@guest
<div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Millones de canciones</h2>
        <p class="text-gray-600">Disfruta de una amplia biblioteca musical con tus artistas favoritos.</p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Crea tus playlists</h2>
        <p class="text-gray-600">Organiza tu música preferida en listas de reproducción personalizadas.</p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Comparte con amigos</h2>
        <p class="text-gray-600">Haz públicas tus playlists y comparte tu música con la comunidad.</p>
    </div>
</div>
@endguest
@endsection
