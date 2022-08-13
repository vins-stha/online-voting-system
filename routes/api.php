<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\QuestionController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::prefix('/user')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [RegisterController::class, 'login']);
    Route::post('/logout', [RegisterController::class, 'logout']);

});

Route::
middleware('auth:sanctum')->
prefix('/v1/users')->group(function () {
    // Route::get('/','UserController@index' );
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'findById']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'delete']);
    Route::post('/{userId}', [UserController::class, 'updateCounter']);
    Route::post('/logout', [RegisterController::class, 'logout']);

});

Route::get('/v1/questions/{id}', [QuestionController::class, 'findById']);

Route::
middleware('auth:sanctum')->
prefix('/v1/questions')->group(function () {
    // Route::get('/','UserController@index' );
    Route::get('/', [QuestionController::class, 'index']);
    Route::put('/{id}', [QuestionController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'delete']);
    Route::post('/', [QuestionController::class, 'askQuestion']);

});
