<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OrderController;

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

    Route::post('/forgotpassword', [ForgotPasswordController::class, 'forgotPasswordUser']);
    Route::post('/resetPassword', [ForgotPasswordController::class, 'resetPassword']);
    

    Route::post('/addbook', [BookController::class, 'addBook']);
    Route::post('/deletebook', [BookController::class, 'deleteBookByBookId']);
    Route::post('/updatebook', [BookController::class, 'updateBookByBookId']);
    Route::get('/getbooks', [BookController::class, 'getAllBooks']);
    Route::get('/searchbooks', [BookController::class, 'searchEnteredKeyWord']);
    Route::get('/sortlowtohigh', [BookController::class, 'sortOnPriceLowToHigh']);
    Route::get('/sorthightolow', [BookController::class, 'sortOnPriceHighToLow']);


    Route::post('/addtocart', [CartController::class, 'addBookToCartByBookId']);
    Route::post('/deletefromcart', [CartController::class, 'deleteBookByCartId']);
    Route::post('/getfromcart', [CartController::class, 'getAllBooksFromCart']);
    Route::post('/updatecart', [CartController::class, 'updateBookQuantityInCart']);

    Route::post('/addAddress', [AddressController::class, 'addAddress']); 
    Route::post('/deleteAddress', [AddressController::class, 'deleteAddress']);
    Route::post('/changeAddress', [AddressController::class, 'changeAddress']);
    Route::get('/getAddress', [AddressController::class, 'getAddress']); 


    Route::post('/placeorder', [OrderController::class, 'placeOrder']);
    Route::post('/sendmail', [OrderController::class, 'sendMailToUserWithOrderId']);
    
});
