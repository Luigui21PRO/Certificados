<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos;
use App\Http\Controllers\Registros;
use App\Http\Controllers\Excel_Documento;

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
    //return view('welcome');
    return redirect('login');
});

Route::get('listar_documentos',[Documentos::class,'listar_documentos'])->name('listar_documentos');
Route::get('tabla_documentos',[Documentos::class,'tabla_documentos'])->name('tabla_documentos');
Route::post('guardar_documentos',[Documentos::class,'guardar_documentos'])->name('guardar_documentos');
Route::get('eliminar_documentos',[Documentos::class,'eliminar_documentos'])->name('eliminar_documentos');

Route::get('listar_Registros',[Registros::class,'listar_Registros'])->name('listar_Registros');
Route::get('tabla_Registros',[Registros::class,'tabla_Registros'])->name('tabla_Registros');
Route::post('guardar_Registros',[Registros::class,'guardar_Registros'])->name('guardar_Registros');
Route::get('eliminar_Registros',[Registros::class,'eliminar_Registros'])->name('eliminar_Registros');

Route::get('excel_Documentos',[Excel_Documento::class,'excel_Documentos'])->name('excel_Documentos');
Route::post('excel_Grabar_Documentos',[Excel_Documento::class,'excel_Grabar_Documentos'])->name('excel_Grabar_Documentos');
Route::get('Seleccionar_Plantilla',[Excel_Documento::class,'Seleccionar_Plantilla'])->name('Seleccionar_Plantilla');
Route::get('fecha',[Excel_Documento::class,'fecha'])->name('fecha');


Route::post('word',[Documentos::class,'word'])->name('word');
Route::get('word',[Documentos::class,'word'])->name('word');
Route::get('generar_word',[Documentos::class,'generar_word'])->name('generar_word');
Route::get('excel_Modificar_Documentos',[Excel_Documento::class,'excel_Modificar_Documentos'])->name('excel_Modificar_Documentos');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
