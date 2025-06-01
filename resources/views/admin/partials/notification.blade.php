<!-- Notificaciones AJAX -->
<div id="notification" class="fixed top-4 right-4 z-50 transform transition-all duration-300 translate-x-full opacity-0">
    <div class="bg-white rounded-lg shadow-lg p-4 max-w-md">
        <div class="flex items-center">
            <div id="notification-icon" class="flex-shrink-0 mr-3">
                <!-- El icono se añadirá dinámicamente mediante JS -->
            </div>
            <div class="flex-1">
                <h3 id="notification-title" class="font-medium">Notificación</h3>
                <p id="notification-message" class="text-sm text-gray-600"></p>
            </div>
            <button type="button" id="close-notification" class="text-gray-400 hover:text-gray-500">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
