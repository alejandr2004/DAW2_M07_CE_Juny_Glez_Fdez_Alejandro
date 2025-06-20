@extends('layouts.app')

@section('content')
@include('admin.partials.notification')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Artistas</h1>
        <div class="space-x-2">
            <a href="{{ route('artists.create') }}" class="bg-spotify hover:bg-spotify-dark text-white py-2 px-4 rounded">
                Nuevo Artista
            </a>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                Volver al Dashboard
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Lista de artistas</h2>
            
            <div>
                <form class="ajax-filter-form flex items-center" data-target="content-container">
                    <div class="relative mr-2">
                        <input type="text" name="search" placeholder="Buscar por nombre..." class="border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify pr-10">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <select name="pais" class="border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                        <option value="">Todos los países</option>
                        @foreach(App\Models\Artist::select('pais')->distinct()->orderBy('pais')->pluck('pais') as $pais)
                            @if($pais)
                                <option value="{{ $pais }}">{{ $pais }}</option>
                            @endif
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        
        <div id="content-container">
            @include('admin.partials.artist-list', ['artists' => $artists])
        </div>
        
        <div id="pagination-container">
            {{ $artists->links('admin.partials.ajax-pagination') }}
        </div>
    </div>
</div>

<!-- Incluir modal de eliminación -->
@include('admin.partials.delete-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
@endpush
