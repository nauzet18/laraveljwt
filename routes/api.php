<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('jwt.auth')->get('/user', function (Request $request) {
    return $request->user();
});
*/
// Ruta registrarse en la aplicación
Route::post('register','AuthenticateController@singup');

// Ruta para loguearse
Route::post('authenticate', 'AuthenticateController@authenticate');

// Ruta para obtener el usuario a partir de un token autentificado
Route::middleware('jwt.auth')->get('authenticate/user', 'AuthenticateController@getAuthenticatedUser');

// Rutas para el recurso de usuario
Route::middleware('jwt.auth')->resource('user', 'UserController', ['except' => [ 'create', 'edit' ]]);
