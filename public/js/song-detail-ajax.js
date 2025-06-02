document.addEventListener('DOMContentLoaded', function() {
    // Referencia al botón de eliminar canción
    const deleteBtn = document.querySelector('.delete-song-btn');
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const songId = this.getAttribute('data-song-id');
            const songTitle = this.getAttribute('data-song-title');
            
            // Mostrar confirmación
            if (confirm(`¿Estás seguro que deseas eliminar la canción "${songTitle}"? Esta acción no se puede deshacer.`)) {
                // Mostrar estado de carga
                deleteBtn.classList.add('opacity-75', 'cursor-not-allowed');
                deleteBtn.textContent = 'Eliminando...';
                
                // Realizar petición AJAX para eliminar
                fetch(`/songs/${songId}/delete-ajax`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Mostrar notificación de éxito
                        showNotification('success', data.message || 'Canción eliminada correctamente');
                        
                        // Redireccionar después de un breve retraso
                        setTimeout(() => {
                            window.location.href = '/songs';
                        }, 1500);
                    } else {
                        // Mostrar error
                        showNotification('error', data.message || 'No se pudo eliminar la canción');
                        resetButton();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'Ha ocurrido un error al eliminar la canción');
                    resetButton();
                });
            }
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
    
    // Función para restaurar el estado del botón
    function resetButton() {
        if (deleteBtn) {
            deleteBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            deleteBtn.textContent = 'Eliminar canción';
        }
    }
});
