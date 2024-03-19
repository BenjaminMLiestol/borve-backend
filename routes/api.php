<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StatisticsController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{userId}', [UserController::class, 'update']);
    Route::delete('/', [UserController::class, 'destroy']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('customers')->group(function () {
    Route::post('/', [CustomerController::class, 'create']);
    Route::get('/', [CustomerController::class, 'index']);
});

Route::group(['prefix' => 'customers/data'], function () {
    Route::get('/total-customers', [StatisticsController::class, 'totalCustomers']);
});

Route::group(['prefix' => 'orders/data'], function () {
    Route::get('/total-revenue', [StatisticsController::class, 'totalRevenue']);
    Route::get('/total-orders', [StatisticsController::class, 'totalOrders']);
});

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/', [OrderController::class, 'update']);
    Route::delete('/', [OrderController::class, 'destroy']);
});

Route::prefix('feedback')->group(function () {
    Route::post('/', [FeedbackController::class, 'store']);
});

 Route::get('/me', [UserController::class, 'me']);
