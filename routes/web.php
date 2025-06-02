<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta de redirección para la raíz
Route::redirect('/', '/login');

// Rutas de autenticación (públicas)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Todas las demás rutas requieren autenticación
Route::get('/test-structure', [\App\Http\Controllers\TestController::class, 'checkStructure']);

Route::middleware('auth')->group(function () {
    // Ruta principal - Página de inicio
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Rutas de exploración de catálogo (protegidas)
    Route::get('/songs', [SongController::class, 'index'])->name('songs.index');
    Route::post('/songs', [SongController::class, 'index']); // Ruta POST para filtros AJAX
    Route::get('/songs/{song}', [SongController::class, 'show'])->name('songs.show');
    Route::get('/albums', [AlbumController::class, 'index'])->name('albums.index');
    Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');
    
    // No hay ruta para reproducir canciones (solo simulación)
    
    // Rutas AJAX para canciones
    Route::get('/songs/data', [SongController::class, 'getSongs'])->name('songs.data');
    Route::post('/songs/{song}/update-ajax', [SongController::class, 'updateAjax'])->name('songs.update.ajax');
    Route::delete('/songs/{song}/delete-ajax', [SongController::class, 'destroyAjax'])->name('songs.delete.ajax');

    // Rutas para el panel de administración
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Gestión de usuarios
        Route::match(['get', 'post'], '/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.updateRole');
        Route::patch('/users/{user}/toggle-disabled', [AdminController::class, 'toggleDisabled'])->name('users.toggleDisabled');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        
        // Gestión de canciones
        Route::match(['get', 'post'], '/songs', [AdminController::class, 'songs'])->name('songs');
        
        // No hay gestión de artistas
        
        // No hay gestión de géneros
        
        // Gestión de álbumes
        Route::match(['get', 'post'], '/albums', [AdminController::class, 'albums'])->name('albums');
    });
    
    // Rutas admin de gestión de canciones
    Route::get('/admin/songs/create', [SongController::class, 'create'])->name('songs.create');
    Route::post('/admin/songs', [SongController::class, 'store'])->name('songs.store');
    Route::get('/admin/songs/{song}/edit', [SongController::class, 'edit'])->name('songs.edit');
    Route::put('/admin/songs/{song}', [SongController::class, 'update'])->name('songs.update');
    Route::delete('/admin/songs/{song}', [SongController::class, 'destroy'])->name('songs.destroy');
    
    // No hay gestión de artistas
    
    // Fallback para cualquier ruta de artistas (las redirige al inicio)
    Route::get('/artists', function() { return redirect()->route('home'); })->name('artists.index');
    Route::get('/artists/{any}', function() { return redirect()->route('home'); })->name('artists.show');
    Route::get('/admin/artists/create', function() { return redirect()->route('admin.dashboard'); })->name('artists.create');
    Route::post('/admin/artists', function() { return redirect()->route('admin.dashboard'); })->name('artists.store');
    Route::get('/admin/artists/{any}/edit', function() { return redirect()->route('admin.dashboard'); })->name('artists.edit');
    Route::put('/admin/artists/{any}', function() { return redirect()->route('admin.dashboard'); })->name('artists.update');
    Route::delete('/admin/artists/{any}', function() { return redirect()->route('admin.dashboard'); })->name('artists.destroy');
    
    // Rutas admin de gestión de álbumes
    Route::get('/admin/albums/create', [AlbumController::class, 'create'])->name('albums.create');
    Route::post('/admin/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::get('/admin/albums/{album}/edit', [AlbumController::class, 'edit'])->name('albums.edit');
    Route::put('/admin/albums/{album}', [AlbumController::class, 'update'])->name('albums.update');
    Route::delete('/admin/albums/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');
    Route::post('/admin/albums/upload-cover', [AlbumController::class, 'uploadCover'])->name('albums.upload-cover');
    
    // Rutas de playlists (para usuarios autenticados)
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::get('/playlists/create', [PlaylistController::class, 'create'])->name('playlists.create');
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');
    Route::get('/playlists/{playlist}/edit', [PlaylistController::class, 'edit'])->name('playlists.edit');
    Route::put('/playlists/{playlist}', [PlaylistController::class, 'update'])->name('playlists.update');
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');
    
    // Rutas para gestionar canciones en playlists
    Route::get('/playlists/{playlist}/add-songs', [PlaylistController::class, 'addSongs'])->name('playlists.add-songs');
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'storeSongs'])->name('playlists.store-songs');
    Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong'])->name('playlists.remove-song');
});
