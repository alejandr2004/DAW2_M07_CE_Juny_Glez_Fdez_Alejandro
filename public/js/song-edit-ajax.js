document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencia al formulario de edición
    const editForm = document.getElementById('songEditForm');
    
    if (editForm) {
        // Prevenir el envío tradicional del formulario
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Referencias a elementos del formulario
            const saveBtn = document.getElementById('saveButton');
            const btnText = saveBtn.querySelector('span') || saveBtn;
            const songId = editForm.getAttribute('data-song-id');
            
            // 1. Mostrar estado de carga
            saveBtn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Guardando...';
            
            // 2. Obtener datos del formulario
            const formData = new FormData(editForm);
            // Asegurarse de que Laravel reconozca esto como una solicitud PUT
            formData.append('_method', 'PUT');
            
            // 3. Realizar petición AJAX
            fetch(`/admin/songs/${songId}`, {
                method: 'POST', // Seguimos usando POST para enviar FormData, pero con _method=PUT
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // 4. Procesar respuesta
                if (data.success) {
                    // 4.1 Mostrar mensaje de éxito
                    showNotification('success', data.message);
                    
                    // 4.2 Actualizar datos en la interfaz si es necesario
                    updateSongInfo(data.song);
                } else {
                    // 4.3 Mostrar errores de validación
                    showErrors(data.errors);
                    showNotification('error', data.message || 'Error al guardar los cambios');
                }
            })
            .catch(error => {
                // 5. Manejar errores
                console.error('Error:', error);
                showNotification('error', 'Ha ocurrido un error al guardar los cambios');
            })
            .finally(() => {
                // 6. Restaurar estado del botón
                saveBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                btnText.textContent = 'Guardar Cambios';
            });
        });
    }
    
    // Función para mostrar notificaciones con SweetAlert
    function showNotification(type, message) {
        Swal.fire({
            icon: type === 'success' ? 'success' : 'error',
            title: type === 'success' ? 'Éxito' : 'Error',
            text: message,
            confirmButtonColor: '#1DB954'
        });
    }
    
    // Función para mostrar errores de validación
    function showErrors(errors) {
        // Limpiar errores anteriores
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid', 'border-red-500');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        
        // Si no hay errores, salir
        if (!errors) return;
        
        // Mostrar nuevos errores
        for (const field in errors) {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid', 'border-red-500');
                
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback text-red-500 text-sm mt-1';
                feedback.textContent = errors[field][0];
                
                input.parentNode.appendChild(feedback);
            }
        }
    }
    
    // Función para actualizar la información de la canción en la interfaz
    function updateSongInfo(song) {
        console.log('Actualizando información de la canción:', song);
        
        // Actualizar el título de la página si existe
        const pageTitle = document.querySelector('h1');
        if (pageTitle && song.title) {
            pageTitle.textContent = `Editar Canción: ${song.title}`;
        }
        
        // Actualizar información del artista
        const artistElement = document.querySelector('.song-artist');
        if (artistElement && song.artist && song.artist.nombre) {
            artistElement.textContent = song.artist.nombre;
        }
        
        // Actualizar información del género
        const genreElement = document.querySelector('.song-genre');
        if (genreElement && song.genre && song.genre.name) {
            genreElement.textContent = song.genre.name;
        }
        
        // Si hay una imagen de portada y se ha actualizado
        if (song.cover_image) {
            const coverImage = document.querySelector('.song-cover-image');
            if (coverImage) {
                coverImage.src = `/storage/${song.cover_image}`;
            }
        }
    }
});
