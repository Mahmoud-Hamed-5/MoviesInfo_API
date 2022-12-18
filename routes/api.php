<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController as AuthController;
use App\Http\Controllers\API\GenresController;
use App\Http\Controllers\API\MoviesController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::controller(AuthController::class)->group(function(){

    Route::post('login', 'login')->name('login');

    Route::post('register', 'register')->name('register');
});


Route::middleware('auth:api')->group(function (){

    Route::controller(MoviesController::class)->group(function(){
        Route::post('movies/{id}', 'update')->name('update');
    });

    Route::resource('movies', MoviesController::class);

});

Route::middleware('auth:api')->group(function (){

    Route::resource('genres', GenresController::class);

});
