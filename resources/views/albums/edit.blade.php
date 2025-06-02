@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Álbum: {{ $album->title }}</h1>
        <a href="{{ route('admin.albums') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
            Volver al Listado
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="editAlbumForm" class="ajax-form" action="{{ route('albums.update', $album) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                        <input type="text" name="title" id="title" value="{{ $album->title }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                        <div class="text-red-500 text-sm error-message" id="title-error"></div>
                    </div>

                    <div class="mb-4">
                        <label for="artist_id" class="block text-sm font-medium text-gray-700 mb-1">Artista</label>
                        <select name="artist_id" id="artist_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                            <option value="">Seleccionar artista</option>
                            @foreach (\App\Models\Artist::orderBy('nombre')->get() as $artist)
                            <option value="{{ $artist->id }}" {{ $album->artist_id == $artist->id ? 'selected' : '' }}>{{ $artist->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="text-red-500 text-sm error-message" id="artist_id-error"></div>
                    </div>

                    <div class="mb-4">
                        <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de lanzamiento</label>
                        <input type="date" name="release_date" id="release_date" value="{{ $album->release_date }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                        <div class="text-red-500 text-sm error-message" id="release_date-error"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="genre_id" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                        <select name="genre_id" id="genre_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                            <option value="">Seleccionar género</option>
                            @foreach (\App\Models\Genre::orderBy('name')->get() as $genre)
                            <option value="{{ $genre->id }}" {{ $album->genre_id == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
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
                                    Seleccionar nueva imagen
                                </button>
                            </div>
                            <div id="imagePreviewContainer" class="{{ $album->cover_image ? '' : 'hidden' }}">
                                <img id="imagePreview" src="{{ $album->cover_image ? asset('storage/' . $album->cover_image) : '#' }}" alt="Vista previa" class="w-24 h-24 object-cover rounded">
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

            <div class="mt-6 text-right">
                <button type="submit" class="btn-spotify py-2 px-6">
                    Actualizar Álbum
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editAlbumForm');
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
                
                fetch('{{ route("albums.upload-cover") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    // Mostrar errores de validación
                    if (data.errors) {
                        for (const [field, messages] of Object.entries(data.errors)) {
                            const errorElement = document.getElementById(`${field}-error`);
                            if (errorElement) {
                                errorElement.textContent = messages[0];
                            }
                        }
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
