@extends('layouts.app')

@section('content')
@include('admin.partials.notification')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Géneros</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
            Volver al Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Crear nuevo género</h2>
        </div>
        
        <form id="createGenreForm" class="ajax-form" action="{{ route('admin.genres.store') }}" method="POST" data-reset="true">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" id="name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <input type="text" name="description" id="description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-spotify hover:bg-spotify-dark text-white py-2 px-4 rounded">
                    Crear Género
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Lista de géneros</h2>
            
            <div>
                <form class="ajax-filter-form flex items-center" data-target="content-container">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Buscar por nombre..." class="border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify pr-10">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div id="content-container">
            @include('admin.partials.genre-list', ['genres' => $genres])
        </div>
        
        <!-- Contenedor para la paginación AJAX -->
        <div id="pagination-container">
            {{ $genres->links('admin.partials.ajax-pagination') }}
        </div>
    </div>
</div>

<!-- Modal para editar género -->
<div id="editGenreModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-md mx-auto">
        <h3 class="text-xl font-bold mb-4">Editar Género</h3>
        
        <form id="editGenreForm" method="POST">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <div class="mb-4">
                <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="name" id="edit_name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify" required>
                <div id="name-error" class="text-red-500 text-sm mt-1"></div>
            </div>
            <div class="mb-4">
                <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <input type="text" name="description" id="edit_description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-spotify focus:border-spotify">
                <div id="description-error" class="text-red-500 text-sm mt-1"></div>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelEditBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded">
                    Cancelar
                </button>
                <button type="submit" id="submitEditBtn" class="bg-spotify hover:bg-spotify-dark text-white py-2 px-4 rounded">
                    Guardar Cambios
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
        // Elementos del DOM
        const editModal = document.getElementById('editGenreModal');
        const editForm = document.getElementById('editGenreForm');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const submitBtn = document.getElementById('submitEditBtn');
        
        // Abrir modal al hacer clic en botón de editar
        document.addEventListener('click', function(e) {
            if (e.target.matches('.edit-genre-btn, .edit-genre-btn *')) {
                const btn = e.target.closest('.edit-genre-btn');
                const genreId = btn.dataset.id;
                const genreName = btn.dataset.name;
                const genreDescription = btn.dataset.description || '';
                
                // Limpiar errores previos
                document.getElementById('name-error').textContent = '';
                document.getElementById('description-error').textContent = '';
                
                // Rellenar el formulario
                document.getElementById('edit_name').value = genreName;
                document.getElementById('edit_description').value = genreDescription;
                
                // Configurar la acción del formulario
                editForm.action = `{{ url('admin/genres') }}/${genreId}`;
                
                // Mostrar modal
                editModal.classList.remove('hidden');
            }
        });
        
        // Manejar envío del formulario
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Obtener el token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Deshabilitar botón mientras se procesa
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">&#8635;</span> Procesando...';
            
            // Crear objeto con los datos del formulario
            const formData = new FormData(editForm);
            
            // Enviar petición AJAX
            fetch(editForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Restaurar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Guardar Cambios';
                
                if (data.success) {
                    // Cerrar modal
                    editModal.classList.add('hidden');
                    
                    // Redirigir a la página de géneros
                    window.location.href = data.redirect || '{{ route("admin.genres") }}';
                } else {
                    // Mostrar errores
                    if (data.errors) {
                        if (data.errors.name) {
                            document.getElementById('name-error').textContent = data.errors.name[0];
                        }
                        if (data.errors.description) {
                            document.getElementById('description-error').textContent = data.errors.description[0];
                        }
                    } else {
                        // Mostrar error general
                        alert(data.error || 'Ha ocurrido un error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Guardar Cambios';
                alert('Error de conexión al servidor');
            });
        });
        
        // Cerrar modal con botón cancelar
        cancelEditBtn.addEventListener('click', function() {
            editModal.classList.add('hidden');
        });
        
        // Cerrar modal al hacer clic fuera
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                editModal.classList.add('hidden');
            }
        });
    });
</script>
@endpush
