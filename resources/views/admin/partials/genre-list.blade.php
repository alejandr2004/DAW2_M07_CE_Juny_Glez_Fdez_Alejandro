@if($genres->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canciones</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($genres as $genre)
                    <tr data-id="{{ $genre->id }}" class="hover:bg-gray-50">
                        <td class="py-3 px-4 whitespace-nowrap">{{ $genre->name }}</td>
                        <td class="py-3 px-4">{{ Str::limit($genre->description, 50) }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $genre->songs_count }}</td>
                        <td class="py-3 px-4 whitespace-nowrap flex gap-2">
                            <button class="edit-genre-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm"
                                    data-id="{{ $genre->id }}"
                                    data-name="{{ $genre->name }}"
                                    data-description="{{ $genre->description }}">
                                Editar
                            </button>
                            <button class="delete-button bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-sm"
                                    data-url="{{ route('admin.genres.destroy', $genre) }}"
                                    data-name="{{ $genre->name }}"
                                    data-type="género">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="pagination-container" class="mt-4">
        {{ $genres->links('partials.pagination') }}
    </div>
@else
    <div class="bg-white p-6 rounded-lg shadow text-center">
        <p class="text-lg text-gray-600">No se encontraron géneros.</p>
    </div>
@endif
