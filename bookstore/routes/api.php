<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ForgotPasswordController;

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

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/loginuser', [AuthController::class, 'loginUser']);
    Route::post('/registeruser', [AuthController::class, 'registerUser']);
    Route::post('/logout', [AuthController::class, 'logoutUser']);
    Route::post('/refresh', [AuthController::class, 'refreshUser']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);

    Route::post('/forgotpassworduser', [ForgotPasswordController::class, 'forgotPasswordUser']);
    Route::post('/forgotpasswordadmin', [ForgotPasswordController::class, 'forgotPasswordAdmin']);


    Route::post('/addbook', [BookController::class, 'addBook']);
    Route::post('/deletebook', [BookController::class, 'delete_BookId']);

});
