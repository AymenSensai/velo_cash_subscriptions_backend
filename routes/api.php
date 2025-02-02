<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{customer}/orders', [CustomerController::class, 'orders']);

    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/restock', [ProductController::class, 'restock']);

    Route::apiResource('orders', OrderController::class);
    Route::apiResource('dashboard', DashboardController::class);

    Route::apiResource('categories', CategoryController::class);
});

Route::post('register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');
