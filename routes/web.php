<?php

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
/**
 * Rutas para el CRUD de categorias
 */
Route::resource('categorias', 'App\Http\Controllers\CategoriaController')->middleware('auth');
Route::get('/categoria/pdf', 'App\Http\Controllers\CategoriaController@generarPdf')->name('categoria.pdf');
Route::get('/categoria/csv', 'App\Http\Controllers\CategoriaController@generarCsv')->name('categoria.csv');
