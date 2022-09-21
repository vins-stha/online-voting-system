<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

use Illuminate\Http\Request;
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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/user/profile', function(){
    return view('profile');
});
Route::get('/users/update', function () {
    return view('update');
});

Route::post('/users/update', [UserController::class, 'update']);
