@if($artists->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagen</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">País</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canciones</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($artists as $artist)
                    <tr data-id="{{ $artist->id }}" class="hover:bg-gray-50">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-full bg-gray-100">
                                    @if($artist->imagen)
                                        <img src="{{ asset('storage/' . $artist->imagen) }}" alt="{{ $artist->nombre }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center bg-gray-300 text-gray-500">
                                            {{ strtoupper(substr($artist->nombre, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $artist->nombre }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $artist->pais }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $artist->songs_count }}</td>
                        <td class="py-3 px-4 whitespace-nowrap flex gap-2">
                            <a href="{{ route('artists.show', $artist) }}" 
                               class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded text-sm">
                                Ver
                            </a>
                            <a href="{{ route('artists.edit', $artist) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm">
                                Editar
                            </a>
                            @if($artist->songs_count == 0)
                                <button class="delete-button bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-sm"
                                        data-url="{{ route('artists.destroy', $artist) }}"
                                        data-name="{{ $artist->nombre }}"
                                        data-type="artista">
                                    Eliminar
                                </button>
                            @else
                                <button class="bg-gray-300 text-gray-500 py-1 px-2 rounded text-sm cursor-not-allowed" 
                                        title="No se puede eliminar porque tiene canciones asociadas">
                                    Eliminar
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="pagination-container" class="mt-4">
        {{ $artists->links('partials.pagination') }}
    </div>
@else
    <div class="bg-white p-6 rounded-lg shadow text-center">
        <p class="text-lg text-gray-600">No se encontraron artistas.</p>
    </div>
@endif
