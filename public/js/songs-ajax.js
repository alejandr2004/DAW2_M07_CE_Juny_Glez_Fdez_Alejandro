document.addEventListener('DOMContentLoaded', function() {
    // 1. Referencias a elementos del DOM
    const tableBody = document.getElementById('songsTableBody');
    const searchInput = document.querySelector('input[name="search"]');
    const filterGenre = document.querySelector('select[name="genre_id"]');
    const filterArtist = document.querySelector('select[name="artist"]');
    const perPageSelect = document.querySelector('select[name="per_page"]') || document.getElementById('perPageSelect');
    const pagination = document.getElementById('pagination-container');
    const clearFiltersBtn = document.querySelector('.clear-filters-btn') || document.getElementById('clearFilters');
    
    // 2. Variables de estado
    let currentPage = 1;
    let perPage = 10;
    
    // 3. Inicializar la carga de datos
    if (tableBody) {
        loadSongs();
    }
    
    // 4. Función principal para cargar datos
    function loadSongs() {
        // 4.1 Mostrar indicador de carga
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="flex justify-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cargando...</div></td></tr>';
        
        // 4.2 Construir URL con parámetros de filtrado y paginación
        let url = `/admin/songs?page=${currentPage}&per_page=${perPage}`;
        
        if (searchInput && searchInput.value) {
            url += '&search=' + encodeURIComponent(searchInput.value);
        }
        
        if (filterGenre && filterGenre.value) {
            url += '&genre_id=' + encodeURIComponent(filterGenre.value);
        }
        
        if (filterArtist && filterArtist.value) {
            url += '&artist=' + encodeURIComponent(filterArtist.value);
        }
        
        // 4.3 Realizar petición fetch
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                // 4.4 Procesar respuesta exitosa
                if (data.html) {
                    // Respuesta del admin controller
                    const contentContainer = document.getElementById('content-container');
                    if (contentContainer) {
                        contentContainer.innerHTML = data.html;
                    }
                    
                    // Actualizar paginación
                    if (pagination && data.pagination) {
                        pagination.innerHTML = data.pagination;
                    }
                    
                    // Volver a inicializar eventos en el contenido actualizado
                    const deleteButtons = document.querySelectorAll('.delete-button');
                    deleteButtons.forEach(button => {
                        button.addEventListener('click', handleDelete);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No se encontraron canciones</td></tr>';
                    if (pagination) pagination.innerHTML = '';
                }
            })
            .catch(error => {
                // 4.5 Manejar errores
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>${error.message}
                </td></tr>`;
                console.error('Error:', error);
            });
    }
    
    // 5. Función para renderizar datos en la tabla
    function renderSongs(songs) {
        if (!songs || songs.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">No se encontraron canciones</td></tr>';
            return;
        }
        
        tableBody.innerHTML = '';
        songs.forEach(song => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', song.id);
            tr.className = 'hover:bg-gray-50';
            
            // Crear celdas con datos
            tr.innerHTML = `
                <td class="py-3 px-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-md bg-gray-100">
                            ${song.cover_image 
                                ? `<img src="/storage/${song.cover_image}" alt="${song.title}" class="h-full w-full object-cover">` 
                                : `<div class="h-full w-full flex items-center justify-center bg-gray-300 text-gray-500">
                                    ${song.title.charAt(0).toUpperCase()}
                                   </div>`
                            }
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">${song.title}</td>
                <td class="py-3 px-4 whitespace-nowrap">${song.artist.nombre}</td>
                <td class="py-3 px-4 whitespace-nowrap">${song.genre.name}</td>
                <td class="py-3 px-4 whitespace-nowrap">${formatDuration(song.duration)}</td>
                <td class="py-3 px-4 whitespace-nowrap flex gap-2">
                    <a href="/songs/${song.id}" 
                       class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded text-sm">
                        Ver
                    </a>
                    <a href="/songs/${song.id}/edit" 
                       class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm">
                        Editar
                    </a>
                    <button class="delete-button bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-sm"
                            data-url="/songs/${song.id}/delete-ajax"
                            data-name="${song.title}"
                            data-type="canción">
                        Eliminar
                    </button>
                </td>
            `;
            
            tableBody.appendChild(tr);
        });
        
        // Agregar event listeners para los botones de eliminar
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', handleDelete);
        });
    }
    
    // 6. Función para renderizar la paginación
    function renderPagination(paginationData) {
        if (!pagination) return;
        
        pagination.innerHTML = '';
        
        // Botón Anterior
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${paginationData.current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link relative block py-1.5 px-3 border border-gray-300 ${paginationData.current_page === 1 ? 'bg-gray-200 text-gray-500' : 'bg-white text-gray-800 hover:bg-gray-100'} transition-all duration-300 rounded" href="#" data-page="${paginationData.current_page - 1}">Anterior</a>`;
        pagination.appendChild(prevLi);
        
        // Páginas numéricas
        for (let i = 1; i <= paginationData.last_page; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = 'page-item';
            pageLi.innerHTML = `<a class="page-link relative block py-1.5 px-3 border border-gray-300 ${i === paginationData.current_page ? 'bg-blue-500 text-white' : 'bg-white text-gray-800 hover:bg-gray-100'} transition-all duration-300 rounded" href="#" data-page="${i}">${i}</a>`;
            pagination.appendChild(pageLi);
        }
        
        // Botón Siguiente
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${paginationData.current_page === paginationData.last_page ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link relative block py-1.5 px-3 border border-gray-300 ${paginationData.current_page === paginationData.last_page ? 'bg-gray-200 text-gray-500' : 'bg-white text-gray-800 hover:bg-gray-100'} transition-all duration-300 rounded" href="#" data-page="${paginationData.current_page + 1}">Siguiente</a>`;
        pagination.appendChild(nextLi);
        
        // Evento para la paginación
        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= paginationData.last_page) {
                    currentPage = page;
                    loadSongs();
                    // Scroll al inicio de la tabla
                    if (tableBody) {
                        tableBody.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    }
    
    // 7. Manejo de eliminación de canciones
    function handleDelete(e) {
        e.preventDefault();
        
        const button = e.currentTarget;
        const url = button.getAttribute('data-url');
        const name = button.getAttribute('data-name');
        const type = button.getAttribute('data-type');
        
        if (confirm(`¿Estás seguro de que deseas eliminar la ${type} "${name}"?`)) {
            // Mostrar estado de carga en el botón
            const originalText = button.innerHTML;
            button.innerHTML = `<svg class="animate-spin -ml-1 mr-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Eliminando...`;
            button.disabled = true;
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Eliminar la fila de la tabla con una animación
                    const row = button.closest('tr');
                    row.style.transition = 'all 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        
                        // Si no quedan filas, recargar para mostrar mensaje "No se encontraron canciones"
                        if (tableBody.querySelectorAll('tr').length === 0) {
                            loadSongs();
                        }
                        
                        // Mostrar notificación de éxito
                        showNotification('success', data.message);
                    }, 300);
                } else {
                    // Restaurar el botón y mostrar error
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showNotification('error', data.message || 'Error al eliminar la canción');
                }
            })
            .catch(error => {
                // Restaurar el botón y mostrar error
                button.innerHTML = originalText;
                button.disabled = false;
                console.error('Error:', error);
                showNotification('error', 'Ha ocurrido un error al eliminar la canción');
            });
        }
    }
    
    // 8. Eventos para filtros y controles
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            currentPage = 1; // Resetear a la primera página al filtrar
            loadSongs();
        }, 500));
    }
    
    if (filterGenre) {
        filterGenre.addEventListener('change', function() {
            currentPage = 1;
            loadSongs();
        });
    }
    
    if (filterArtist) {
        filterArtist.addEventListener('change', function() {
            currentPage = 1;
            loadSongs();
        });
    }
    
    // 9. Cambiar registros por página
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            perPage = parseInt(this.value);
            currentPage = 1; // Resetear a primera página
            loadSongs();
        });
    }
    
    // 10. Limpiar filtros
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (filterGenre) filterGenre.value = '';
            if (filterArtist) filterArtist.value = '';
            currentPage = 1;
            loadSongs();
        });
    }
    
    // 11. Función utilidad para debounce (evitar múltiples llamadas)
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // 12. Función utilidad para formatear duración
    function formatDuration(seconds) {
        if (!seconds) return '00:00';
        return new Date(seconds * 1000).toISOString().substr(14, 5);
    }
    
    // 13. Función para mostrar notificaciones con SweetAlert
    function showNotification(type, message) {
        Swal.fire({
            icon: type === 'success' ? 'success' : 'error',
            title: type === 'success' ? 'Éxito' : 'Error',
            text: message,
            confirmButtonColor: '#1DB954'
        });
    }
    
    // 14. Exportar funciones útiles para usar en otros contextos
    window.songAjax = {
        reload: loadSongs,
        showNotification: showNotification
    };
});
