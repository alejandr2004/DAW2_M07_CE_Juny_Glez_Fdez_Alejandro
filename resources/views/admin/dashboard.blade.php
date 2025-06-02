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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold mb-2">Usuarios</h3>
                <p class="text-3xl font-bold text-spotify">{{ \App\Models\User::count() }}</p>
                <a href="{{ route('admin.users') }}" class="text-spotify hover:underline mt-2 inline-block">Gestionar usuarios</a>
            </div>
            
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold mb-2">Canciones</h3>
                <p class="text-3xl font-bold text-spotify">{{ \App\Models\Song::count() }}</p>
                <a href="{{ route('admin.songs') }}" class="text-spotify hover:underline mt-2 inline-block">Gestionar canciones</a>
            </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4">Acciones rápidas</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('albums.create') }}" class="btn-spotify py-2 px-4 rounded">Añadir álbum</a>
            <a href="{{ route('songs.create') }}" class="btn-spotify py-2 px-4 rounded">Añadir canción</a>
        </div>


    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
@endpush
