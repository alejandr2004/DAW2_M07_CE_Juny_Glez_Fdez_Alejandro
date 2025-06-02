@if($users->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha registro</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr data-id="{{ $user->id }}" class="hover:bg-gray-50">
                        <td class="py-3 px-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $user->role === 'admin' ? 'Administrador' : 'Usuario' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_disabled ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $user->is_disabled ? 'Deshabilitado' : 'Activo' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 whitespace-nowrap flex gap-2">
                            @if(Auth::id() !== $user->id)
                                
                                <form class="ajax-form" action="{{ route('admin.users.toggleDisabled', $user) }}" method="POST" data-reset="false">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $user->is_disabled ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-500 hover:bg-gray-600' }} text-white py-1 px-2 rounded text-sm">
                                        {{ $user->is_disabled ? 'Habilitar' : 'Deshabilitar' }}
                                    </button>
                                </form>
                                
                                <button class="delete-button bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-sm"
                                        data-url="{{ route('admin.users.destroy', $user) }}"
                                        data-name="{{ $user->name }}"
                                        data-type="usuario">
                                    Eliminar
                                </button>
                            @else
                                <span class="text-sm text-gray-500">Tu cuenta</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="bg-white p-6 rounded-lg shadow text-center">
        <p class="text-lg text-gray-600">No se encontraron usuarios.</p>
    </div>
@endif
