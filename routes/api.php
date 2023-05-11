<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CategoriesController;

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


Route::controller(PostsController::class)->group(function () {
    Route::get('posts', 'index'); //LISTAR TODOS LOS POST (PÚBLICO, OK)
    Route::post('posts/addpost', 'store'); // CREAR POST (USUARIO LOGUEADO, ARROJA ERROR SI NO ESTA LOGUEADO)
    Route::get('posts/from/{category}', 'articlesByCategory'); //LISTAR LOS POST DE UNA CATEGORIA (PÚBLICO)
    Route::get('post/{id}', 'show'); // MUESTRA UN POST EN PARTICULAR (PÚBLICO)
    Route::delete('posts/destroy/{id}', 'destroy'); //SE BORRA SI EL USUARIO ES DUEÑO DEL POST O SI ES ADMIN
    Route::post('post/update', 'update'); //UPDATE POST
    Route::get('posts/user', 'postsFromUser'); //MUESTRA LOS POST DE UN USUARIO MEDIANTE TOKEN
});

Route::controller(CategoriesController::class)->group(function () {
    Route::post('categories/create', 'store'); // OK, LO CREA SOLO SI ES USUARIO ADMIN
    Route::get('categories/delete/{id}', 'destroy'); //OK, LO BORRA SOLO SI ES ADMIN
});

Route::controller(UsersController::class)->group(function () {
    Route::get('user/wallet', 'walletFromUser'); //TRAE OK LA WALLET DEL USUARIO
});


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login'); //LOGIN OK
    Route::post('register', 'register'); // REGISTER OK SOLO PARA USUARIO NORMALES, UN ADMIN YA EN BBDD
    Route::post('logout', 'logout'); // LOGOUT OK
    Route::get('me', 'me'); // ME, OK.
});