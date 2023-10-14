<?php

use App\Http\Controllers\UbicacionController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');  //cambiar
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::resource('personas', 'App\Http\Controllers\UserController')->middleware('auth');

Route::get('/clientes', 'App\Http\Controllers\UserController@clientes')->name('clientes.index');
Route::get('/administradores', 'App\Http\Controllers\UserController@administradores')->name('administradores.index');

Route::prefix('ubicaciones')->middleware('auth')->group(function () {
    Route::get('{cliente_id}', [UbicacionController::class, 'index'])->name('ubicaciones.index');
    Route::get('create/{cliente_id}', [UbicacionController::class, 'create'])->name('ubicaciones.create');
    Route::post('store/{cliente_id}', [UbicacionController::class, 'store'])->name('ubicaciones.store');
    Route::get('edit/{ubicacion_id}', [UbicacionController::class, 'edit'])->name('ubicaciones.edit');
    Route::put('update/{ubicacion_id}', [UbicacionController::class, 'update'])->name('ubicaciones.update');
    Route::delete('destroy/{ubicacion_id}', [UbicacionController::class, 'destroy'])->name('ubicaciones.destroy');
});

Route::resource('categorias', 'App\Http\Controllers\CategoriaController')->middleware('auth');
/**
 * Rutas para el CRUD de categorias
 */
Route::resource('categorias', 'App\Http\Controllers\CategoriaController')->middleware('auth');


/*
 * Rutas para el CRUD de productos
*/
Route::resource('productos', 'App\Http\Controllers\ProductoController')->middleware('auth');
