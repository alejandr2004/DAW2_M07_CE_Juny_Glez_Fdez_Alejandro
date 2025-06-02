@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-6">Explora Canciones</h1>

    <!-- Filtros de búsqueda -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form id="song-filter-form" action="{{ route('songs.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                <!-- Búsqueda por título o artista -->
                <div class="md:col-span-4">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search" id="search" class="form-control w-full" 
                        value="{{ request('search') }}" placeholder="Título o artista">
                </div>
                
                <!-- Selección de artista -->
                <div class="md:col-span-4">
                    <label for="artist" class="block text-sm font-medium text-gray-700 mb-1">Artista</label>
                    <select name="artist" id="artist" class="form-control w-full">
                        <option value="">Todos los artistas</option>
                        @foreach($artists as $artist)
                            <option value="{{ $artist->nombre }}" {{ request('artist') == $artist->nombre ? 'selected' : '' }}>
                                {{ $artist->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Botones de acción -->
                <div class="md:col-span-4 self-end">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" class="btn-spotify py-2 px-4 w-full">Aplicar filtros</button>
                        <button type="button" id="reset-filters-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded">Limpiar</button>
                    </div>
                </div>
            </div>
            
            <!-- Filtros de género múltiples y sumativos -->
            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Géneros (selección múltiple)</label>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    @foreach($genres as $genre)
                        <div class="flex items-center">
                            <input type="checkbox" name="genres[]" id="genre-{{ $genre->id }}" value="{{ $genre->id }}" 
                                {{ in_array($genre->id, $selectedGenres) ? 'checked' : '' }} class="mr-2">
                            <label for="genre-{{ $genre->id }}" class="text-sm">{{ $genre->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <!-- Indicador de carga -->
    <div id="loading-indicator" class="hidden">
        <div class="bg-white p-3 mb-4 rounded shadow text-center">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-spotify mr-2"></div>
            <span>Cargando canciones...</span>
        </div>
    </div>

    <!-- Lista de canciones -->
    <div id="songs-container">
    @if($songs->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($songs as $song)
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative pb-[100%] bg-gray-200 overflow-hidden">
                        @php
                            // Crear colores pseudo-aleatorios basados en el ID de la canción
                            $songId = $song->id;
                            $hue = ($songId * 137) % 360; // Distribución uniforme de colores en el espectro
                            $colorPrimary = "hsla({$hue}, 80%, 40%, 0.9)";
                            $colorSecondary = "hsla({$hue}, 70%, 30%, 0.8)";
                            $patternType = $songId % 5; // 5 patrones diferentes
                        @endphp
                        
                        <div class="absolute inset-0" style="background: linear-gradient(135deg, {{$colorPrimary}}, {{$colorSecondary}});">
                            @if($patternType == 0)
                                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.3) 2px, transparent 2px), radial-gradient(circle at 80% 40%, rgba(255,255,255,0.3) 2px, transparent 2px), radial-gradient(circle at 40% 70%, rgba(255,255,255,0.3) 2px, transparent 2px); background-size: 30px 30px;"></div>
                            @elseif($patternType == 1)
                                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(45deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent); background-size: 20px 20px;"></div>
                            @elseif($patternType == 2)
                                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(to right, rgba(255,255,255,0.2) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 20px 20px;"></div>
                            @elseif($patternType == 3)
                                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle, rgba(255,255,255,0.3) 20%, transparent 20%), radial-gradient(circle, rgba(255,255,255,0.3) 20%, transparent 20%); background-size: 30px 30px; background-position: 0 0, 15px 15px;"></div>
                            @else
                                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(135deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent); background-size: 20px 20px;"></div>
                            @endif
                            
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center text-white w-full h-full flex flex-col items-center justify-center">
                                    <div class="rounded-full bg-white bg-opacity-20 p-3">
                                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1 truncate">{{ $song->title }}</h3>
                        <p class="text-gray-600 mb-2 truncate">{{ $song->artist->name }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ $song->duration }}</span>
                            <a href="{{ route('songs.show', $song) }}" class="text-spotify hover:underline">Ver detalles</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-8">
            {{ $songs->links() }}
        </div>
    @else
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <p class="text-lg text-gray-600">No se encontraron canciones con los filtros seleccionados.</p>
            @if(request('search') || request()->has('genres') || request('artist'))
                <a href="{{ route('songs.index') }}" class="btn-spotify inline-block mt-4 py-2 px-4">Ver todas las canciones</a>
            @endif
        </div>
    @endif
    </div>

    @auth
        @if(auth()->user()->role === 'admin')
            <div class="mt-8">
                <a href="{{ route('songs.create') }}" class="btn-spotify py-2 px-4 rounded inline-block">Añadir nueva canción</a>
            </div>
        @endif
    @endauth
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('song-filter-form');
        const songsContainer = document.getElementById('songs-container');
        const loadingIndicator = document.getElementById('loading-indicator');
        const resetBtn = document.getElementById('reset-filters-btn');
        const searchInput = document.getElementById('search');
        const artistSelect = document.getElementById('artist');
        const genreCheckboxes = document.querySelectorAll('input[name="genres[]"]');
        
        // Manejar envío del formulario
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            loadSongsViaAjax();
        });

        // Manejar click en botón de reset
        resetBtn.addEventListener('click', function() {
            form.reset();
            loadSongsViaAjax();
        });

        // Delegación de eventos para la paginación
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                const pageUrl = paginationLink.href;
                const pageNumber = new URL(pageUrl).searchParams.get('page') || 1;
                loadSongsViaAjax(pageNumber);
            }
        });

        // Función para cargar canciones con AJAX
        function loadSongsViaAjax(page = 1) {
            // Mostrar indicador de carga
            loadingIndicator.classList.remove('hidden');

            // Construir FormData del formulario
            const formData = new FormData(form);
            formData.append('page', page);
            
            // Añadir token CSRF
            formData.append('_token', '{{ csrf_token() }}');

            // Realizar petición fetch
            fetch('{{ route("songs.index") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                songsContainer.innerHTML = data.html;
                
                // Actualizar URL con parámetros sin recargar la página
                const params = new URLSearchParams();
                if (searchInput.value) params.append('search', searchInput.value);
                if (artistSelect.value) params.append('artist', artistSelect.value);
                
                genreCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        params.append('genres[]', checkbox.value);
                    }
                });
                
                if (page > 1) params.append('page', page);
                
                const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
                window.history.pushState({ path: newUrl }, '', newUrl);
            })
            .catch(error => {
                console.error('Error:', error);
                songsContainer.innerHTML = `
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <p class="text-red-600">Ocurrió un error al cargar las canciones. Por favor, inténtalo de nuevo.</p>
                    </div>
                `;
            })
            .finally(() => {
                // Ocultar indicador de carga
                loadingIndicator.classList.add('hidden');
            });
        }
    });
</script>
@endsection
