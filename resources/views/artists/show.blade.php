@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('artists.index') }}" class="text-spotify hover:underline">
            &larr; Volver a artistas
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/4 mb-6 md:mb-0 md:mr-8">
                    <div class="w-full aspect-square rounded-lg overflow-hidden">
                        @if($artist->imagen)
                            <img src="{{ asset('storage/' . $artist->imagen) }}" alt="{{ $artist->nombre }}" 
                                class="w-full h-full object-cover rounded-lg shadow">
                        @else
                            @php
                                // Crear colores pseudo-aleatorios basados en el ID del artista
                                $artistId = $artist->id;
                                $hue = ($artistId * 107) % 360;
                                $colorPrimary = "hsla({$hue}, 65%, 45%, 0.9)";
                                $colorSecondary = "hsla({$hue}, 75%, 25%, 0.8)";
                            @endphp
                            
                            <div class="w-full h-full relative" style="background: linear-gradient(135deg, {{$colorPrimary}}, {{$colorSecondary}});">
                                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: linear-gradient(30deg, rgba(255,255,255,0.15) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.15) 87.5%, rgba(255,255,255,0.15)), linear-gradient(150deg, rgba(255,255,255,0.15) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.15) 87.5%, rgba(255,255,255,0.15)), linear-gradient(30deg, rgba(255,255,255,0.15) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.15) 87.5%, rgba(255,255,255,0.15)), linear-gradient(150deg, rgba(255,255,255,0.15) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.15) 87.5%, rgba(255,255,255,0.15)), linear-gradient(60deg, rgba(255,255,255,0.1) 25%, transparent 25.5%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1)), linear-gradient(60deg, rgba(255,255,255,0.1) 25%, transparent 25.5%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1)); background-size: 40px 70px; background-position: 0 0, 0 0, 20px 35px, 20px 35px, 0 0, 20px 35px;"></div>
                                
                                <div class="flex items-center justify-center h-full">
                                    <div class="text-center text-white">
                                        <div class="rounded-full bg-white bg-opacity-20 p-4 mx-auto">
                                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M18 10.849c0 .966-.784 1.75-1.75 1.75h-1v1a.75.75 0 01-1.5 0v-1h-1.75a1.75 1.75 0 110-3.5H13v-1h-.75a.75.75 0 110-1.5H13v-1h.25a1.75 1.75 0 110-3.5H12v-.5a.75.75 0 00-1.5 0v.5H9.75a1.75 1.75 0 110 3.5H10v1h.25a.75.75 0 110 1.5H10v1h-.75a1.75 1.75 0 110 3.5H10v1a.75.75 0 001.5 0v-1h.75c.966 0 1.75-.784 1.75-1.75v-.25h1v.25c0 .966.784 1.75 1.75 1.75h.25a.75.75 0 000-1.5H16.5v-1.75c0-.966-.784-1.75-1.75-1.75h-.25v-.25c0-.966-.784-1.75-1.75-1.75h-.25v.25c0 .966.784 1.75 1.75 1.75h.25v1.75c0 .138.112.25.25.25h1.75a.25.25 0 00.25-.25z" />
                                            </svg>
                                        </div>
                                        <div class="mt-4 text-2xl font-bold">{{ strtoupper(substr($artist->nombre, 0, 1)) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="md:w-3/4">
                    <h1 class="text-3xl font-bold mb-4">{{ $artist->nombre }}</h1>
                    
                    @if($artist->biografia)
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold mb-2">Biografía</h2>
                            <p class="text-gray-700">{{ $artist->biografia }}</p>
                        </div>
                    @endif

                    @if($artist->pais)
                        <div class="mb-6">
                            <p class="text-gray-600"><span class="font-semibold">País:</span> {{ $artist->pais }}</p>
                        </div>
                    @endif

                    <!-- Álbumes del artista (si existen) -->
                    @if(isset($albums) && $albums->count() > 0)
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold mb-3">Álbumes</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($albums as $album)
                                    <div class="bg-gray-100 p-3 rounded-lg">
                                        @if($album->cover_image)
                                            <img src="{{ asset('storage/' . $album->cover_image) }}" 
                                                alt="{{ $album->title }}" class="w-full h-auto mb-2 rounded">
                                        @endif
                                        <h3 class="font-medium">{{ $album->title }}</h3>
                                        <p class="text-gray-500 text-sm">{{ $album->release_year }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Canciones del artista -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-3">Canciones</h2>
                        @if($artist->songs->count() > 0)
                            <div class="bg-gray-50 rounded-lg">
                                <div class="grid grid-cols-1 divide-y divide-gray-200">
                                    @foreach($artist->songs as $song)
                                        <div class="p-3 hover:bg-gray-100">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <a href="{{ route('songs.show', $song) }}" class="font-medium hover:text-spotify">
                                                        {{ $song->title }}
                                                    </a>
                                                    <p class="text-gray-500 text-sm">
                                                        {{ $song->genre->name }} · {{ $song->duration }}
                                                    </p>
                                                </div>
                                                <div class="text-gray-400 text-sm">
                                                    {{ number_format($song->play_count) }} reproducciones
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-gray-600">No hay canciones disponibles para este artista.</p>
                        @endif
                    </div>

                    @auth
                        @if(auth()->user()->role === 'admin')
                            <div class="mt-8 pt-4 border-t border-gray-200">
                                <h3 class="text-lg font-semibold mb-2">Opciones de administrador</h3>
                                <div class="flex space-x-2">
                                    <a href="{{ route('artists.edit', $artist) }}" class="btn-secondary py-1 px-3 text-sm">
                                        Editar artista
                                    </a>
                                    <button id="deleteArtistBtn" class="btn-danger py-1 px-3 text-sm">Eliminar artista</button>
                                </div>
                            </div>
                            
                            <!-- Modal de confirmación -->
                            <div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                                <div class="bg-white p-6 rounded-lg shadow-lg max-w-md mx-auto">
                                    <h3 class="text-xl font-bold mb-4">Confirmar eliminación</h3>
                                    <p class="mb-6">¿Estás seguro de que quieres eliminar a "{{ $artist->nombre }}"? Esta acción no se puede deshacer.</p>
                                    <div class="flex justify-end space-x-3">
                                        <button id="cancelDeleteBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded">
                                            Cancelar
                                        </button>
                                        <button id="confirmDeleteBtn" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                                            Sí, eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notificación -->
                            <div id="notification" class="fixed top-4 right-4 p-4 rounded shadow-lg transform translate-x-full transition-transform duration-300 z-50">
                                <p id="notificationMessage"></p>
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Referencias a elementos
                                    const deleteBtn = document.getElementById('deleteArtistBtn');
                                    const modal = document.getElementById('deleteConfirmModal');
                                    const cancelBtn = document.getElementById('cancelDeleteBtn');
                                    const confirmBtn = document.getElementById('confirmDeleteBtn');
                                    const notification = document.getElementById('notification');
                                    const notificationMsg = document.getElementById('notificationMessage');
                                    
                                    // Abrir modal
                                    deleteBtn.addEventListener('click', function() {
                                        modal.classList.remove('hidden');
                                    });
                                    
                                    // Cerrar modal
                                    cancelBtn.addEventListener('click', function() {
                                        modal.classList.add('hidden');
                                    });
                                    
                                    // Confirmar eliminación
                                    confirmBtn.addEventListener('click', function() {
                                        // Mostrar cargando
                                        confirmBtn.innerHTML = '<span class="inline-block animate-spin mr-2">&#8635;</span> Eliminando...';
                                        confirmBtn.disabled = true;
                                        
                                        // Petición AJAX para eliminar artista
                                        fetch('{{ route('artists.destroy', $artist) }}', {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            // Cerrar modal
                                            modal.classList.add('hidden');
                                            
                                            if (data.success) {
                                                // Mostrar notificación de éxito
                                                notification.classList.add('bg-green-100', 'text-green-800', 'border-l-4', 'border-green-500');
                                                notificationMsg.textContent = data.message || 'Artista eliminado correctamente';
                                                notification.classList.remove('translate-x-full');
                                                
                                                // Redirigir después de 1.5 segundos
                                                setTimeout(() => {
                                                    window.location.href = '{{ route('artists.index') }}';
                                                }, 1500);
                                            } else {
                                                // Mostrar notificación de error
                                                notification.classList.add('bg-red-100', 'text-red-800', 'border-l-4', 'border-red-500');
                                                notificationMsg.textContent = data.error || 'Error al eliminar el artista';
                                                notification.classList.remove('translate-x-full');
                                                
                                                // Ocultar notificación después de 5 segundos
                                                setTimeout(() => {
                                                    notification.classList.add('translate-x-full');
                                                }, 5000);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            modal.classList.add('hidden');
                                            
                                            // Mostrar notificación de error
                                            notification.classList.add('bg-red-100', 'text-red-800', 'border-l-4', 'border-red-500');
                                            notificationMsg.textContent = 'Error en la comunicación con el servidor';
                                            notification.classList.remove('translate-x-full');
                                            
                                            // Ocultar notificación después de 5 segundos
                                            setTimeout(() => {
                                                notification.classList.add('translate-x-full');
                                            }, 5000);
                                        });
                                    });
                                });
                            </script>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
