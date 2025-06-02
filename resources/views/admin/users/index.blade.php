@extends('layouts.app')

@section('content')
@include('admin.partials.notification')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
            Volver al Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
            <h2 class="text-lg font-semibold">Lista de usuarios</h2>
            
            <div class="w-full md:w-auto">
                <form action="{{ route('admin.users') }}" method="POST" class="ajax-filter-form flex flex-col md:flex-row items-start md:items-center gap-2" data-target="content-container">
                    @csrf
                    <div class="relative w-full md:w-auto">
                        <input type="text" name="search" placeholder="Buscar por nombre o email..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify pr-10">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <select name="role" class="w-full md:w-auto border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                        <option value="">Todos los roles</option>
                        <option value="admin">Administradores</option>
                        <option value="user">Usuarios</option>
                    </select>
                    <select name="sort" class="w-full md:w-auto border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                        <option value="name">Ordenar por nombre</option>
                        <option value="email">Ordenar por email</option>
                        <option value="created_at">Fecha de registro</option>
                    </select>
                    <button type="button" class="clear-filters-btn bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md w-full md:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Limpiar filtros
                    </button>
                </form>
            </div>
        </div>
        
        <div id="content-container">
            @include('admin.partials.user-list', ['users' => $users])
        </div>
        
        <!-- Contenedor para la paginación AJAX -->
        <div id="pagination-container">
            {{ $users->links('admin.partials.ajax-pagination') }}
        </div>
    </div>
</div>

<!-- Incluir modal de eliminación -->
@include('admin.partials.delete-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
@endpush
