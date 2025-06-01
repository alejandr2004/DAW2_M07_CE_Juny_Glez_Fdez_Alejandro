<!-- Modal de confirmación para eliminar -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-md mx-auto">
        <h3 class="text-xl font-bold mb-4">Confirmar eliminación</h3>
        <p id="deleteMessage" class="mb-6"></p>
        
        <div class="flex justify-end space-x-3">
            <button id="cancelDeleteBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded">
                Cancelar
            </button>
            <form id="deleteForm" method="POST" class="ajax-form" data-reset="false">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                    Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
