<?php
use App\Http\Controllers\CatalogoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PagoController;

// Home → redirige al catálogo
Route::get('/', fn() => redirect()->route('catalogo.index'));

// Catálogo público
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/{slug}', [CatalogoController::class, 'show'])->name('catalogo.show');

// Rutas autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
});
// Carrito
Route::prefix('carrito')->name('carrito.')->middleware('auth')->group(function () {
    Route::get('/',                              [CarritoController::class, 'index'])     ->name('index');
    Route::post('/agregar/{producto}',           [CarritoController::class, 'agregar'])   ->name('agregar');
    Route::patch('/actualizar/{carrito}',        [CarritoController::class, 'actualizar'])->name('actualizar');
    Route::delete('/eliminar/{carrito}',         [CarritoController::class, 'eliminar'])  ->name('eliminar');
    Route::delete('/vaciar',                     [CarritoController::class, 'vaciar'])    ->name('vaciar');
    Route::post('/transporte',                   [CarritoController::class, 'setTransporte'])->name('transporte');
});

// Órdenes
Route::prefix('ordenes')->name('orden.')->middleware('auth')->group(function () {
    Route::get('/checkout',          [OrdenController::class, 'checkout']) ->name('checkout');
    Route::post('/store',            [OrdenController::class, 'store'])    ->name('store');
    Route::get('/historial',         [OrdenController::class, 'historial'])->name('historial');
    Route::get('/{orden}',           [OrdenController::class, 'show'])     ->name('show');
    Route::get('/{orden}/pago',      [OrdenController::class, 'pago'])     ->name('pago');
    Route::post('/{orden}/cancelar', [OrdenController::class, 'cancelar']) ->name('cancelar');
});

// Pagos MercadoPago
Route::prefix('pago')->name('pago.')->group(function () {
    Route::post('/preferencia/{orden}', [PagoController::class, 'crearPreferencia'])->name('preferencia')->middleware('auth');
    Route::get('/success',              [PagoController::class, 'success'])           ->name('success');
    Route::get('/failure',              [PagoController::class, 'failure'])           ->name('failure');
    Route::get('/pending',              [PagoController::class, 'pending'])           ->name('pending');

    // Eliminamos el withoutMiddleware porque ya lo configuramos en bootstrap/app.php
    Route::post('/webhook',             [PagoController::class, 'webhook'])           ->name('webhook');
});

// Rutas solo admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
});

require __DIR__ . '/auth.php';

