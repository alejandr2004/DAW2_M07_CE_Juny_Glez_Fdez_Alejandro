@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg overflow-hidden shadow-lg p-6">
    <h2 class="text-2xl font-bold text-center mb-6">Iniciar sesión</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
            <input id="password" type="password" name="password" required
                   class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="mr-2">
                <span class="text-sm text-gray-700">Recordarme</span>
            </label>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="btn-spotify py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Iniciar sesión
            </button>
            <a href="{{ route('register') }}" class="inline-block align-baseline font-bold text-sm text-spotify hover:text-green-600">
                ¿No tienes cuenta?
            </a>
        </div>
    </form>
</div>
@endsection
