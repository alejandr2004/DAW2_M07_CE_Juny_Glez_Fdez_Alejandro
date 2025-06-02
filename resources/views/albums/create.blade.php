@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Crear Nuevo Álbum</h1>
        <a href="{{ route('admin.albums') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
            Volver al Listado
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="createAlbumForm" class="ajax-form" action="{{ route('albums.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                        <input type="text" name="title" id="title" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                        <div class="text-red-500 text-sm error-message" id="title-error"></div>
                    </div>

                    <div class="mb-4">
                        <label for="artist_id" class="block text-sm font-medium text-gray-700 mb-1">Artista</label>
                        <select name="artist_id" id="artist_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                            <option value="">Seleccionar artista</option>
                            @foreach ($artists as $artist)
                            <option value="{{ $artist->id }}">{{ $artist->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="text-red-500 text-sm error-message" id="artist_id-error"></div>
                    </div>

                    <div class="mb-4">
                        <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de lanzamiento</label>
                        <input type="date" name="release_date" id="release_date" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                        <div class="text-red-500 text-sm error-message" id="release_date-error"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="genre_id" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                        <select name="genre_id" id="genre_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                            <option value="">Seleccionar género</option>
                            @foreach (\App\Models\Genre::orderBy('name')->get() as $genre)
                            <option value="{{ $genre->id }}">{{ $genre->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-red-500 text-sm error-message" id="genre_id-error"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-4">
                        <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1">Imagen de portada</label>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <input type="file" name="cover_image" id="cover_image" class="hidden" accept="image/*">
                                <input type="hidden" name="temp_cover_path" id="temp_cover_path">
                                <button type="button" id="selectImageBtn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded">
                                    Seleccionar imagen
                                </button>
                            </div>
                            <div id="imagePreviewContainer" class="hidden relative">
                                <img id="imagePreview" src="#" alt="Vista previa" class="w-24 h-24 object-cover rounded">
                                <button type="button" id="removeImageBtn" class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="text-red-500 text-sm error-message" id="cover_image-error"></div>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">Canciones del álbum</h3>
                <p class="text-sm text-gray-600 mb-4">Puedes añadir canciones a este álbum o hacerlo más tarde.</p>
                
                <div id="songs-container" class="space-y-4">
                    <!-- Aquí se añadirán dinámicamente las filas de canciones -->
                    <div class="song-row grid grid-cols-1 md:grid-cols-5 gap-4 items-center border-b pb-2">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Título</label>
                            <input type="text" name="songs[0][title]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Título de la canción">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Duración (mm:ss)</label>
                            <input type="text" name="songs[0][duration]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="3:45">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-song bg-red-500 text-white p-2 rounded-md hover:bg-red-600" title="Eliminar canción">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="button" id="add-song" class="border border-gray-400 bg-white text-gray-700 py-2 px-4 rounded-md hover:bg-gray-100 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Añadir otra canción
                    </button>
                </div>
            </div>

            <div class="mt-6 text-right">
                <button type="submit" class="btn-spotify py-2 px-6">
                    Crear Álbum
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createAlbumForm');
        const selectImageBtn = document.getElementById('selectImageBtn');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const coverImageInput = document.getElementById('cover_image');
        const tempCoverPathInput = document.getElementById('temp_cover_path');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        
        // Manejar selección de imagen
        selectImageBtn.addEventListener('click', function() {
            coverImageInput.click();
        });
        
        // Mostrar vista previa de imagen
        coverImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.classList.remove('hidden');
                }
                
                reader.readAsDataURL(this.files[0]);
                
                // Subir imagen mediante AJAX
                const formData = new FormData();
                formData.append('cover_image', this.files[0]);
                // Añadir el token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                formData.append('_token', csrfToken);
                
                fetch('{{ route("albums.upload-cover") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                        // No incluir Content-Type para que el navegador establezca el boundary correcto para FormData
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        tempCoverPathInput.value = data.path;
                    } else {
                        console.error('Error al subir imagen:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
        
        // Eliminar imagen
        removeImageBtn.addEventListener('click', function() {
            coverImageInput.value = '';
            tempCoverPathInput.value = '';
            imagePreview.src = '#';
            imagePreviewContainer.classList.add('hidden');
        });
        
        // Manejar envío del formulario mediante AJAX
        // Gestión dinámica de canciones
        let songCounter = 1; // Empezamos en 1 porque ya tenemos una fila inicial (0)
        
        // Añadir nueva canción
        document.getElementById('add-song').addEventListener('click', function() {
            const songsContainer = document.getElementById('songs-container');
            
            const newSongRow = document.createElement('div');
            newSongRow.className = 'song-row grid grid-cols-1 md:grid-cols-5 gap-4 items-center border-b pb-2';
            newSongRow.innerHTML = `
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Título</label>
                    <input type="text" name="songs[${songCounter}][title]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Título de la canción">
                    <div class="error-message text-red-500 text-sm mt-1" id="songs.${songCounter}.title-error"></div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Duración (mm:ss)</label>
                    <input type="text" name="songs[${songCounter}][duration]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="3:45">
                    <div class="error-message text-red-500 text-sm mt-1" id="songs.${songCounter}.duration-error"></div>
                </div>
                <div class="flex items-end">
                    <button type="button" class="remove-song bg-red-500 text-white p-2 rounded-md hover:bg-red-600" title="Eliminar canción">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            `;
            songsContainer.appendChild(newSongRow);
            songCounter++;
        });

        // Eliminar una canción (usando delegación de eventos)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-song')) {
                const songRow = e.target.closest('.song-row');
                
                // Efecto de fade out y luego eliminar
                songRow.style.opacity = '0';
                songRow.style.transition = 'opacity 0.3s';
                
                setTimeout(() => {
                    songRow.remove();
                    
                    // Verificar si no queda ninguna fila y añadir una vacía
                    const songRows = document.querySelectorAll('.song-row');
                    if (songRows.length === 0) {
                        const songsContainer = document.getElementById('songs-container');
                        const emptyRow = document.createElement('div');
                        emptyRow.className = 'song-row grid grid-cols-1 md:grid-cols-5 gap-4 items-center border-b pb-2';
                        emptyRow.innerHTML = `
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="songs[0][title]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Título de la canción">
                                <div class="error-message text-red-500 text-sm mt-1" id="songs.0.title-error"></div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Duración (mm:ss)</label>
                                <input type="text" name="songs[0][duration]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="3:45">
                                <div class="error-message text-red-500 text-sm mt-1" id="songs.0.duration-error"></div>
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-song bg-red-500 text-white p-2 rounded-md hover:bg-red-600" title="Eliminar canción">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        `;
                        songsContainer.appendChild(emptyRow);
                        songCounter = 1;
                    }
                }, 300);
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Limpiar mensajes de error previos
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
            });
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                    // No incluir Content-Type para que el navegador establezca el boundary correcto para FormData
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito con SweetAlert
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message || 'Álbum creado correctamente',
                        icon: 'success',
                        confirmButtonColor: '#1DB954',
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    // Mostrar errores de validación
                    if (data.errors) {
                        for (const [field, messages] of Object.entries(data.errors)) {
                            const errorElement = document.getElementById(`${field}-error`);
                            if (errorElement) {
                                errorElement.textContent = messages[0];
                            }
                        }
                        // Mostrar mensaje de error general con SweetAlert
                        Swal.fire({
                            title: 'Error',
                            text: 'Por favor, corrija los errores en el formulario',
                            icon: 'error',
                            confirmButtonColor: '#1DB954',
                        });
                    } else if (data.error) {
                        // Mostrar mensaje de error general
                        Swal.fire({
                            title: 'Error',
                            text: data.error,
                            icon: 'error',
                            confirmButtonColor: '#1DB954',
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
</script>
@endpush
@endsection
