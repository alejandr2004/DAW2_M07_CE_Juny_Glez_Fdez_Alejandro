@extends('layouts.app')

@section('content')
@include('admin.partials.notification')
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
                        <label for="release_year" class="block text-sm font-medium text-gray-700 mb-1">Año de lanzamiento</label>
                        <input type="number" name="release_year" id="release_year" min="1900" max="{{ date('Y') + 1 }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                        <div class="text-red-500 text-sm error-message" id="release_year-error"></div>
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
                            <div id="image-preview" class="h-32 w-32 bg-gray-200 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-red-500 text-sm error-message" id="cover_image-error"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-spotify hover:bg-spotify-dark text-white py-2 px-6 rounded">
                    Guardar Álbum
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const coverInput = document.getElementById('cover_image');
        const previewContainer = document.getElementById('image-preview');
        const selectBtn = document.getElementById('selectImageBtn');
        const tempPathInput = document.getElementById('temp_cover_path');
        
        // Abrir selector de archivos
        selectBtn.addEventListener('click', function() {
            coverInput.click();
        });
        
        // Previsualizar imagen seleccionada
        coverInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewContainer.innerHTML = `<img src="${e.target.result}" class="h-32 w-32 object-cover">`;
                    
                    // Si tenemos AJAX upload configurado
                    uploadCoverImage();
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Subir imagen de portada vía AJAX
        function uploadCoverImage() {
            const formData = new FormData();
            formData.append('cover_image', coverInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route("albums.upload-cover") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tempPathInput.value = data.path;
                } else {
                    console.error('Error al subir imagen:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
</script>
@endpush
