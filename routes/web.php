<?php
use App\Http\Controllers\CatalogoController;
use Illuminate\Support\Facades\Route;

// Home → redirige al catálogo
Route::get('/', fn() => redirect()->route('catalogo.index'));

// Catálogo público
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/{slug}', [CatalogoController::class, 'show'])->name('catalogo.show');

// Rutas autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
});

// Rutas solo admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
});

require __DIR__ . '/auth.php';

