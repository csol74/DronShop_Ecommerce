<?php
use App\Http\Controllers\CatalogoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\Admin\OrdenController as AdminOrdenController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\ProveedorController as AdminProveedorController;

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
    Route::get('/dashboard',                            [DashboardController::class, 'index'])          ->name('dashboard');

    // Productos
    Route::get('/productos',                            [AdminProductoController::class, 'index'])       ->name('productos.index');
    Route::get('/productos/crear',                      [AdminProductoController::class, 'create'])      ->name('productos.create');
    Route::post('/productos',                           [AdminProductoController::class, 'store'])       ->name('productos.store');
    Route::get('/productos/{producto}/editar',          [AdminProductoController::class, 'edit'])        ->name('productos.edit');
    Route::put('/productos/{producto}',                 [AdminProductoController::class, 'update'])      ->name('productos.update');
    Route::delete('/productos/{producto}',              [AdminProductoController::class, 'destroy'])     ->name('productos.destroy');
    Route::patch('/productos/{producto}/toggle',        [AdminProductoController::class, 'toggleActivo'])->name('productos.toggle');

    // Órdenes
    Route::get('/ordenes',                              [AdminOrdenController::class, 'index'])          ->name('ordenes.index');
    Route::get('/ordenes/{orden}',                      [AdminOrdenController::class, 'show'])           ->name('ordenes.show');
    Route::patch('/ordenes/{orden}/estado',             [AdminOrdenController::class, 'actualizarEstado'])->name('ordenes.estado');

    // Usuarios
    Route::get('/usuarios',                             [UsuarioController::class, 'index'])             ->name('usuarios.index');
    Route::patch('/usuarios/{user}/rol',                [UsuarioController::class, 'cambiarRol'])        ->name('usuarios.rol');

    // Proveedores
    Route::get('/proveedores',                          [AdminProveedorController::class, 'index'])      ->name('proveedores.index');
    Route::get('/proveedores/crear',                    [AdminProveedorController::class, 'create'])     ->name('proveedores.create');
    Route::post('/proveedores',                         [AdminProveedorController::class, 'store'])      ->name('proveedores.store');
    Route::get('/proveedores/{proveedor}/editar',       [AdminProveedorController::class, 'edit'])       ->name('proveedores.edit');
    Route::put('/proveedores/{proveedor}',              [AdminProveedorController::class, 'update'])     ->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}',           [AdminProveedorController::class, 'destroy'])    ->name('proveedores.destroy');
});

require __DIR__ . '/auth.php';

