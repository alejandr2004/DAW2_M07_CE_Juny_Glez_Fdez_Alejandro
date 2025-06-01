<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySpotify - @yield('title', 'Bienvenido')</title>
    <!-- Tailwind CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Estilos adicionales -->
    <style>
        .bg-spotify {
            background-color: #1DB954;
        }
        .text-spotify {
            color: #1DB954;
        }
        .btn-spotify {
            background-color: #1DB954;
            color: white;
            transition: background-color 0.3s;
        }
        .btn-spotify:hover {
            background-color: #1ed760;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <header class="bg-black text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold flex items-center">
                <span class="text-spotify mr-2">MySpotify</span>
            </a>
            
            <nav>
                <ul class="flex space-x-6">
                    @guest
                        <li><a href="{{ route('login') }}" class="hover:text-spotify">Iniciar sesión</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-spotify">Registrarse</a></li>
                    @else
                        @if(auth()->user()->role === 'admin')
                            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-spotify">Panel de administración</a></li>
                        @endif
                        <li><a href="{{ route('playlists.index') }}" class="hover:text-spotify">Mis playlists</a></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="hover:text-spotify">Cerrar sesión</button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 flex-grow">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-black text-white py-6">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} MySpotify. Todos los derechos reservados.</p>
                <div class="mt-2">
                    <a href="#" class="hover:text-spotify mr-4">Términos y condiciones</a>
                    <a href="#" class="hover:text-spotify">Política de privacidad</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
