<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/','UserController@listar');
Route::post('registrar','UserController@registrar');
Route::get('cambiarclave','UserController@cambiarPassword');
Route::get('cambiarestado','UserController@cambiarEstado');
Route::get('actualizar','UserController@actualizar');
Route::get('incubado','UserController@listarIncubados');
Route::get('especialidad','EspecialidadController@listarEspecialidades');


Route::get('especialidad/{id}','EspecialidadController@listarEspecialidadesPorEvaluador');



