/**
 * Funciones AJAX para la administración del sitio
 */

document.addEventListener('DOMContentLoaded', function() {
    // Configurar el token CSRF para todas las peticiones AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Configurar fetch para incluir el token CSRF en todas las peticiones
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        if (!options.headers) {
            options.headers = {};
        }
        
        // Añadir el token CSRF a todas las peticiones
        if (!options.headers['X-CSRF-TOKEN']) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        
        // Añadir el encabezado X-Requested-With para identificar peticiones AJAX
        if (!options.headers['X-Requested-With']) {
            options.headers['X-Requested-With'] = 'XMLHttpRequest';
        }
        
        return originalFetch(url, options);
    };
    
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

        // Manejar búsqueda con debounce reducido para actualizaciones más rápidas
        const searchInputs = form.querySelectorAll('input[type="text"], input[type="search"]');
        searchInputs.forEach(input => {
            let debounceTimer;
            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    submitFilterForm(form);
                }, 200); // Reducido a 200ms para una respuesta más rápida
            });
        });

        // Evitar envío tradicional del formulario
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitFilterForm(form);
        });
        
        // Manejar botones de limpiar filtros
        const clearButton = form.querySelector('.clear-filters-btn');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                form.reset();
                submitFilterForm(form);
            });
        }
        
        // Manejar botones de reset específicos
        const resetButton = document.getElementById('reset-filters');
        if (resetButton) {
            resetButton.addEventListener('click', () => {
                form.reset();
                submitFilterForm(form);
            });
        }
        
        // Manejar los clics en los enlaces de paginación
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                const url = new URL(paginationLink.href);
                const pageParam = url.searchParams.get('page');
                
                if (pageParam) {
                    // Crear o actualizar el input oculto para la página
                    let pageInput = form.querySelector('input[name="page"]');
                    if (!pageInput) {
                        pageInput = document.createElement('input');
                        pageInput.type = 'hidden';
                        pageInput.name = 'page';
                        form.appendChild(pageInput);
                    }
                    pageInput.value = pageParam;
                    
                    // Enviar el formulario
                    submitFilterForm(form);
                }
            }
        });
    });
}

/**
 * Envía el formulario de filtro mediante AJAX usando Fetch API
 */
function submitFilterForm(form) {
    const targetContainerId = form.dataset.target || 'content-container';
    const targetContainer = document.getElementById(targetContainerId);
    const paginationContainer = document.getElementById('pagination-container');
    const formData = new FormData(form);
    
    // Añadir token CSRF al FormData si no existe
    if (!formData.has('_token')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', csrfToken);
    }
    
    // Mostrar indicador de carga
    if (targetContainer) {
        targetContainer.innerHTML = '<div class="flex justify-center items-center p-12"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-spotify"></div></div>';
    }
    
    const url = form.getAttribute('action');
    
    // Depurar qué datos se están enviando
    console.log('Enviando datos del formulario:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Estado de la respuesta:', response.status);
        
        // Si tenemos errores de validación (422), continuamos pero logueamos los errores
        if (response.status === 422) {
            return response.json().then(data => {
                console.error('Errores de validación:', data.errors || data);
                // En lugar de lanzar un error, intentamos cargar la página sin filtros
                window.location.href = url.split('?')[0]; // Recargar sin parámetros
                return { html: 'Recargando...', pagination: '' };
            });
        }
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos del servidor:', data);
        
        // Actualizar el contenido
        if (data.html) {
            if (targetContainer) {
                targetContainer.innerHTML = data.html;
            }
            
            // Actualizar la paginación si existe
            if (paginationContainer && data.pagination) {
                paginationContainer.innerHTML = data.pagination;
            }
            
            // Re-inicializar los botones de acción
            initializeAdminActions();
            
            // Actualizar URL para permitir recargar la página
            updateUrlWithFormData(formData);
        }
    })
    .catch(error => {
        console.error('Error en la petición Fetch:', error);
        if (targetContainer) {
            targetContainer.innerHTML = '<div class="p-4 bg-red-100 text-red-700 rounded">Error al cargar los resultados: ' + error.message + '</div>';
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
                body: method !== 'GET' ? formData : null,
                credentials: 'same-origin'
            })
            .then(response => {
                // Si la respuesta no es OK, lanzar un error para manejarlo en el catch
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || `Error en la respuesta: ${response.status}`);
                    });
                }
                return response.json();
            })
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
                        
                        // Si es el formulario de toggle disabled, recargar la página
                        if (url.includes('toggleDisabled')) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
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
                    // Actualizar la lista completa mediante AJAX
                    const contentContainer = document.getElementById('content-container');
                    const paginationContainer = document.getElementById('pagination-container');
                    
                    if (contentContainer) {
                        // Obtener la URL actual sin parámetros
                        const currentUrl = window.location.pathname;
                        // Recargar la lista completa
                        loadContentViaAjax(currentUrl, contentContainer, paginationContainer);
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
    
    console.log('Cargando contenido AJAX desde URL:', url);
    
    // Extraer parámetros de la URL para la petición POST
    const urlObj = new URL(url, window.location.origin);
    const params = new URLSearchParams(urlObj.search);
    
    // Crear FormData con los parámetros de la URL
    const formData = new FormData();
    for (const [key, value] of params.entries()) {
        formData.append(key, value);
        console.log('Parámetro:', key, value);
    }
    
    // Añadir el token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData.append('_token', csrfToken);
    
    // Verificar si es una URL de paginación y si contiene el parámetro page
    if (!formData.has('page') && url.includes('page=')) {
        const pageMatch = url.match(/page=(\d+)/);
        if (pageMatch && pageMatch[1]) {
            formData.append('page', pageMatch[1]);
            console.log('Añadido parámetro de página:', pageMatch[1]);
        }
    }
    
    console.log('Enviando petición POST a:', urlObj.pathname);
    
    // Realizar petición AJAX usando POST
    fetch(urlObj.pathname, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
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
            const page = link.getAttribute('data-page');
            const contentContainer = document.getElementById('content-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            console.log('Click en paginación AJAX:', { url, page });
            
            if (contentContainer && url) {
                // Crear una URL con el parámetro de página
                const currentPath = window.location.pathname;
                const currentParams = new URLSearchParams(window.location.search);
                
                // Actualizar o añadir el parámetro page
                if (page) {
                    currentParams.set('page', page);
                }
                
                // Construir la nueva URL
                const newUrl = currentPath + '?' + currentParams.toString();
                console.log('URL para paginación:', newUrl);
                
                loadContentViaAjax(newUrl, contentContainer, paginationContainer);
            }
        }
    });
}

/**
 * Muestra una notificación en la pantalla usando SweetAlert
 */
function showNotification(message, type = 'success') {
    Swal.fire({
        icon: type,
        title: type === 'success' ? 'Éxito' : 'Error',
        text: message,
        confirmButtonColor: '#1DB954'
    });
}

/**
 * Las funciones específicas para el formulario de canciones han sido eliminadas
 * ya que ahora todos los formularios usan la implementación estándar de submitFilterForm    

// Esta función ha sido reemplazada por submitFilterForm

/**
 * Actualiza la URL del navegador con los parámetros del formulario
 */
function updateUrlWithFormData(formData) {
    const params = new URLSearchParams();
    
    formData.forEach((value, key) => {
        // No incluir el token CSRF ni valores vacíos en la URL
        if (key !== '_token' && value) {
            params.append(key, value);
        }
    });
    
    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.pushState({}, '', newUrl);
}
