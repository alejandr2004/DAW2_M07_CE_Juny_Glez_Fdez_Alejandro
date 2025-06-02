<!-- No mostrar filtros aquí, se muestran en la vista principal -->

<!-- Tabla de canciones -->
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-100">
            <tr>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Portada</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artista</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Género</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración</th>
                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody id="songsTableBody" class="divide-y divide-gray-200">
            <!-- La tabla se llenará dinámicamente con AJAX -->
            @if($songs->count() > 0)
                @foreach($songs as $song)
                    <tr data-id="{{ $song->id }}" class="hover:bg-gray-50">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-md bg-gray-100">
                                    @if($song->cover_image)
                                        <img src="{{ asset('storage/' . $song->cover_image) }}" alt="{{ $song->title }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center bg-gray-300 text-gray-500">
                                            {{ strtoupper(substr($song->title, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $song->title }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $song->artist->nombre }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $song->genre->name }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ gmdate("i:s", (int)$song->duration) }}</td>
                        <td class="py-3 px-4 whitespace-nowrap flex gap-2">
                            <a href="{{ route('songs.show', $song) }}" 
                               class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded text-sm">
                                Ver
                            </a>
                            <a href="{{ route('songs.edit', $song) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm">
                                Editar
                            </a>
                            <button class="delete-button bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-sm"
                                    data-url="/songs/{{ $song->id }}/delete-ajax"
                                    data-name="{{ $song->title }}"
                                    data-type="canción">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No se encontraron canciones.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Paginación -->
<div class="mt-4">
    <ul id="pagination" class="flex justify-center space-x-1">
        <!-- La paginación se generará dinámicamente con JavaScript -->
    </ul>
</div>

<!-- No incluir script AJAX aquí ya que se carga desde admin.js -->
