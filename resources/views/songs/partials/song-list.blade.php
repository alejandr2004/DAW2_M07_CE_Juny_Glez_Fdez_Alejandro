@if($songs->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
        @foreach($songs as $song)
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col h-full">
                <div class="relative pb-[100%] bg-gray-200 w-full">
                    @if($song->cover_image)
                        <img src="{{ asset('storage/' . $song->cover_image) }}" alt="{{ $song->title }}" 
                            class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-700 to-gray-900">
                            <span class="text-white text-2xl font-bold">{{ strtoupper(substr($song->title, 0, 2)) }}</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 
                                flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                        <a href="{{ route('songs.show', $song) }}" class="text-white bg-spotify rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="p-4 flex-grow flex flex-col">
                    <h3 class="font-bold text-lg mb-1 line-clamp-1">{{ $song->title }}</h3>
                    <p class="text-gray-600 mb-2 line-clamp-1">
                        <!-- Removido enlace a artista ya que no existe ruta artists.show -->
                        {{ $song->artist ? $song->artist->nombre : 'Artista desconocido' }}
                    </p>
                    <div class="flex items-center justify-between mt-auto pt-2">
                        <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-xs rounded-full">
                            {{ $song->genre->name }}
                        </span>
                        <span class="text-gray-500 text-sm">{{ gmdate("i:s", (int)$song->duration) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div id="pagination-container">
        {{ $songs->links() }}
    </div>
@else
    <div class="bg-white p-6 rounded-lg shadow text-center">
        <p class="text-lg text-gray-600">No se encontraron canciones con los filtros seleccionados.</p>
    </div>
@endif
