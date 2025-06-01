/**
 * Funciones AJAX para la administración del sitio
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar manejadores
    initializeFilters();
    initializeAdminActions();
    initializeAjaxForms();
    initializeAjaxPagination();
});

/**
 * Inicializa los filtros AJAX para tablas de administración
 */
function initializeFilters() {
    const filterForms = document.querySelectorAll('.ajax-filter-form');
    
    filterForms.forEach(form => {
        // Manejar filtros al cambiar selects
        const selectInputs = form.querySelectorAll('select');
        selectInputs.forEach(select => {
            select.addEventListener('change', () => {
                submitFilterForm(form);
            });
        });

        // Manejar filtros al cambiar checkboxes
        const checkboxInputs = form.querySelectorAll('input[type="checkbox"]');
        checkboxInputs.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                submitFilterForm(form);
            });
        });

        // Manejar búsqueda con debounce
        const searchInputs = form.querySelectorAll('input[type="text"], input[type="search"]');
        searchInputs.forEach(input => {
            let debounceTimer;
            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    submitFilterForm(form);
                }, 500); // Esperar 500ms después de que el usuario deje de escribir
            });
        });

        // Evitar envío tradicional del formulario
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitFilterForm(form);
        });
    });
}

/**
 * Envía el formulario de filtro mediante AJAX
 */
function submitFilterForm(form) {
    const targetContainerId = form.dataset.target || 'content-container';
    const targetContainer = document.getElementById(targetContainerId);
    const paginationContainer = document.getElementById('pagination-container');
    const formData = new FormData(form);
    const url = form.action;

    // Mostrar indicador de carga
    if (targetContainer) {
        targetContainer.innerHTML = '<div class="flex justify-center items-center p-12"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-spotify"></div></div>';
    }

    // Realizar petición AJAX
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        // No podemos enviar FormData directamente en GET, convertimos a parámetros de URL
        // y los añadimos a la URL
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la petición AJAX');
        }
        return response.json();
    })
    .then(data => {
        if (targetContainer) {
            targetContainer.innerHTML = data.html || '';
        }
        if (paginationContainer && data.pagination) {
            paginationContainer.innerHTML = data.pagination;
        }
        // Actualizar URL para reflejar los filtros
        window.history.pushState({}, '', form.action + '?' + new URLSearchParams(formData).toString());
    })
    .catch(error => {
        console.error('Error:', error);
        if (targetContainer) {
            targetContainer.innerHTML = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>Error al cargar los datos. Por favor, inténtelo de nuevo.</p></div>';
        }
    });
}

/**
 * Inicializa las acciones administrativas AJAX
 */
function initializeAdminActions() {
    // Delegación de eventos para elementos que pueden ser añadidos dinámicamente
    document.addEventListener('click', function(event) {
        // Manejo de botones de eliminación
        if (event.target.matches('.delete-button, .delete-button *')) {
            const button = event.target.closest('.delete-button');
            event.preventDefault();
            
            // Obtener datos del botón
            const deleteUrl = button.dataset.url;
            const itemName = button.dataset.name || 'este elemento';
            const itemType = button.dataset.type || 'elemento';
            
            if (deleteUrl) {
                confirmDelete(deleteUrl, itemName, itemType);
            }
        }
        
        // Manejo de enlaces de paginación AJAX
        if (event.target.matches('.ajax-pagination a, .ajax-pagination a *')) {
            const link = event.target.closest('a');
            event.preventDefault();
            
            const url = link.href;
            const targetContainer = document.getElementById('content-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            loadContentViaAjax(url, targetContainer, paginationContainer);
        }
    });

    // Inicializar formularios AJAX
    initializeAjaxForms();
}

/**
 * Inicializa formularios AJAX
 */
function initializeAjaxForms() {
    const ajaxForms = document.querySelectorAll('.ajax-form');
    
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const url = form.action;
            const method = form.method.toUpperCase();
            const submitButton = form.querySelector('[type="submit"]');
            const originalButtonText = submitButton ? submitButton.innerHTML : '';
            
            // Mostrar estado de carga en el botón
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="inline-block animate-spin mr-2">&#8635;</span> Procesando...';
            }
            
            // Incluir el token CSRF y el método para peticiones PUT/DELETE
            if (method === 'PUT' || method === 'DELETE') {
                formData.append('_method', method);
            }
            
            // Enviar petición AJAX
            fetch(url, {
                method: method === 'GET' ? 'GET' : 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: method !== 'GET' ? formData : null
            })
            .then(response => response.json())
            .then(data => {
                // Restaurar botón
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
                
                // Manejar respuesta
                if (data.success) {
                    showNotification(data.message || 'Operación completada con éxito', 'success');
                    
                    // Si hay redirección, ir a esa URL
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        // Limpiar formulario si es necesario
                        if (form.dataset.reset === 'true') {
                            form.reset();
                        }
                        
                        // Actualizar contenido si es necesario
                        if (data.html && form.dataset.updateTarget) {
                            const updateTarget = document.getElementById(form.dataset.updateTarget);
                            if (updateTarget) {
                                updateTarget.innerHTML = data.html;
                            }
                        }
                    }
                } else {
                    // Mostrar errores de validación
                    if (data.errors) {
                        showFormErrors(form, data.errors);
                    } else {
                        showNotification(data.error || 'Ha ocurrido un error', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Restaurar botón
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
                
                showNotification('Error en la comunicación con el servidor', 'error');
            });
        });
    });
}

/**
 * Muestra errores de validación en un formulario
 */
function showFormErrors(form, errors) {
    // Limpiar errores anteriores
    form.querySelectorAll('.error-message').forEach(el => el.remove());
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Mostrar nuevos errores
    for (const field in errors) {
        const input = form.querySelector(`[name="${field}"], [name="${field}[]"]`);
        if (input) {
            input.classList.add('is-invalid');
            
            // Añadir mensaje de error debajo del campo
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-red-500 text-sm mt-1';
            errorDiv.textContent = errors[field][0]; // Tomar solo el primer mensaje de error
            
            const parentElement = input.parentElement;
            parentElement.appendChild(errorDiv);
        }
    }
    
    // Mostrar notificación general
    showNotification('Por favor, corrija los errores en el formulario', 'error');
    
    // Scroll al primer error
    const firstErrorField = form.querySelector('.is-invalid');
    if (firstErrorField) {
        firstErrorField.focus();
        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

/**
 * Muestra un diálogo de confirmación para eliminar un elemento
 */
function confirmDelete(url, itemName, itemType) {
    // Crear modal de confirmación
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.id = 'deleteConfirmModal';
    
    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Confirmar eliminación</h3>
            <p class="mb-6">¿Estás seguro de que quieres eliminar ${itemType} "${itemName}"? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDeleteBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded">
                    Cancelar
                </button>
                <button id="confirmDeleteBtn" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                    Sí, eliminar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Manejar botones
    document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.innerHTML = '<span class="inline-block animate-spin mr-2">&#8635;</span> Eliminando...';
        confirmBtn.disabled = true;
        
        // Enviar petición AJAX para eliminar
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.body.removeChild(modal);
            
            if (data.success) {
                showNotification(data.message || `${itemType} eliminado correctamente`, 'success');
                
                // Si hay redirección, ir a esa URL
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    // Eliminar el elemento de la lista si existe
                    const item = document.querySelector(`[data-id="${data.id}"]`);
                    if (item) {
                        item.remove();
                    }
                    
                    // Actualizar contenido si es necesario
                    if (data.html && data.updateTarget) {
                        const updateTarget = document.getElementById(data.updateTarget);
                        if (updateTarget) {
                            updateTarget.innerHTML = data.html;
                        }
                    }
                }
            } else {
                showNotification(data.error || 'Error al eliminar el elemento', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.body.removeChild(modal);
            showNotification('Error en la comunicación con el servidor', 'error');
        });
    });
}

/**
 * Carga contenido vía AJAX
 */
function loadContentViaAjax(url, targetContainer, paginationContainer) {
    if (!targetContainer) return;
    
    // Mostrar indicador de carga
    targetContainer.innerHTML = '<div class="flex justify-center items-center p-12"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-spotify"></div></div>';
    
    // Realizar petición AJAX
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la petición AJAX');
        }
        return response.json();
    })
    .then(data => {
        targetContainer.innerHTML = data.html || '';
        
        if (paginationContainer && data.pagination) {
            paginationContainer.innerHTML = data.pagination;
        }
        
        // Actualizar URL
        window.history.pushState({}, '', url);
    })
    .catch(error => {
        console.error('Error:', error);
        targetContainer.innerHTML = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>Error al cargar los datos. Por favor, inténtelo de nuevo.</p></div>';
    });
}

/**
 * Inicializa los enlaces de paginación AJAX
 */
function initializeAjaxPagination() {
    // Utilizamos delegación de eventos para manejar los enlaces de paginación que se añaden dinámicamente
    document.addEventListener('click', function(event) {
        if (event.target.closest('.ajax-pagination-link')) {
            event.preventDefault();
            const link = event.target.closest('.ajax-pagination-link');
            const url = link.getAttribute('href');
            const contentContainer = document.getElementById('content-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            if (contentContainer && url) {
                loadContentViaAjax(url, contentContainer, paginationContainer);
            }
        }
    });
}

/**
 * Muestra una notificación en la pantalla
 */
function showNotification(message, type = 'success') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded shadow-lg transform transition-transform duration-300 z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : 'bg-red-100 text-red-800 border-l-4 border-red-500'
    }`;
    notification.style.transform = 'translateX(100%)';
    notification.innerHTML = `<p>${message}</p>`;
    
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Eliminar después de un tiempo
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}
