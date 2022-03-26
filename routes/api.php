<?php

use App\Http\Controllers\CustomerController as Customer;
use App\Http\Controllers\OrderController as Order;
use App\Http\Controllers\UserController as User;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('login', [User::class, 'authenticate']);
Route::post('register', [User::class, 'register']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [User::class, 'logout']);

    Route::get('customer/all', [Customer::class, 'getData']);
    Route::get('customer/id/{id}', [Customer::class, 'getByID']);
    Route::get('customer/search', [Customer::class, 'searchData']);
    Route::post('customer/save', [Customer::class, 'save']);
    Route::delete('customer/delete/{id}', [Customer::class, 'delete']);

    Route::get('order/all', [Order::class, 'getData']);
    Route::get('order/id/{id}', [Order::class, 'getByID']);
    Route::get('order/search', [Customer::class, 'searchData']);
    Route::post('order/save', [Order::class, 'save']);
    Route::delete('order/delete/{id}', [Order::class, 'delete']);
  
});
