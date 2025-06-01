@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('content')
<!-- Incluir notificaciones AJAX -->
@include('admin.partials.notification')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="bg-black text-white p-4">
        <h1 class="text-2xl font-bold">Panel de Administración</h1>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold mb-2">Usuarios</h3>
                <p class="text-3xl font-bold text-spotify">{{ \App\Models\User::count() }}</p>
                <a href="{{ route('admin.users') }}" class="text-spotify hover:underline mt-2 inline-block">Gestionar usuarios</a>
            </div>
            
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold mb-2">Artistas</h3>
                <p class="text-3xl font-bold text-spotify">{{ \App\Models\Artist::count() }}</p>
                <a href="{{ route('admin.artists') }}" class="text-spotify hover:underline mt-2 inline-block">Gestionar artistas</a>
            </div>
            
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold mb-2">Canciones</h3>
                <p class="text-3xl font-bold text-spotify">{{ \App\Models\Song::count() }}</p>
                <a href="{{ route('admin.songs') }}" class="text-spotify hover:underline mt-2 inline-block">Gestionar canciones</a>
            </div>
            
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold mb-2">Géneros</h3>
                <p class="text-3xl font-bold text-spotify">{{ \App\Models\Genre::count() }}</p>
                <a href="{{ route('admin.genres') }}" class="text-spotify hover:underline mt-2 inline-block">Gestionar géneros</a>
            </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4">Acciones rápidas</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('artists.create') }}" class="btn-spotify py-2 px-4 rounded">Añadir artista</a>
            <a href="{{ route('albums.create') }}" class="btn-spotify py-2 px-4 rounded">Añadir álbum</a>
            <a href="{{ route('songs.create') }}" class="btn-spotify py-2 px-4 rounded">Añadir canción</a>
            <a href="#" class="bg-gray-800 text-white py-2 px-4 rounded hover:bg-gray-700">Ver estadísticas</a>
        </div>

        <h2 class="text-2xl font-semibold mt-8 mb-4">Últimos usuarios registrados</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Nombre</th>
                        <th class="py-2 px-4 text-left">Email</th>
                        <th class="py-2 px-4 text-left">Rol</th>
                        <th class="py-2 px-4 text-left">Estado</th>
                        <th class="py-2 px-4 text-left">Fecha registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4">{{ $user->name }}</td>
                        <td class="py-2 px-4">{{ $user->email }}</td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded text-xs {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded text-xs {{ $user->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="py-2 px-4">{{ $user->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
@endpush
