<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VoteController;
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
    Route::get('/answercount/{id}', [UserController::class, 'userPoints']);
});

Route::get('/users/{id}', [UserController::class, 'findById']);

Route::middleware('auth:sanctum')->get('/user/getid', [UserController::class, 'getUserId']);

Route::
middleware('auth:sanctum')->
prefix('/v1/users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'findById']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'delete']);
    Route::post('/{userId}', [UserController::class, 'updateCounter']);
    Route::post('/logout', [RegisterController::class, 'logout']);

});


Route::
middleware('auth:sanctum')->
prefix('/v1/questions')->group(function () {
    Route::get('/user/{uid}', [QuestionController::class, 'findQuestionsByUserId']);
    Route::put('/{id}', [QuestionController::class, 'update']);
    Route::delete('/{id}', [QuestionController::class, 'delete']);
    Route::post('/', [QuestionController::class, 'askQuestion']);
    Route::post('/vote/{qid}/{votetype}', [QuestionController::class, 'addVote']);
});

Route::get('/v1/question-search/tags={tags}', [QuestionController::class, 'listQuestionsByTag']);

Route::get('/v1/questions', [QuestionController::class, 'index']);

Route::get('/v1/questions/{id}', [QuestionController::class, 'findById']);


Route::
middleware('auth:sanctum')->
prefix('/v1/answer')->group(function () {
    Route::get('/', [AnswerController::class, 'index']);
    Route::post('/vote/{aid}/{votetype}', [AnswerController::class, 'addVote']);
    Route::post('/{qid}', [AnswerController::class, 'answer']);
    Route::put('/{id}', [AnswerController::class, 'update']);
    Route::delete('/{id}', [AnswerController::class, 'delete']);

});

Route::
middleware('auth:sanctum')->
prefix('/v1/tags')->group(function () {
    Route::post('/', [TagController::class, 'create']);
});
Route::get('v1/tags/', [TagController::class, 'index']);

Route::
middleware('auth:sanctum')->
prefix('/v1/votes')->group(function () {
    Route::post('/duplicate/{item}/{itemid}', [QuestionController::class, 'handleReportDuplicate']);
    Route::post('/{item}/{itemid}/{votetype}', [VoteController::class, 'vote']);
 
});