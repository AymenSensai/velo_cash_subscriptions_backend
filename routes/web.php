<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SubscriptionController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

Route::get('/orders/{order}/details', [OrderController::class, 'orderDetails']);
Route::get('/payment-success', function() {
    return view('payment-success');
});

Route::put('/orders/{order}/pay', [OrderController::class, 'pay']);

Route::get('/subscriptions/{customer}/details', [SubscriptionController::class, 'subscriptionDetails']);
Route::get('/subscription-success', function() {
    return view('subscriptions.subscription-success');
});
Route::put('/subscriptions/{customer}/pay', [SubscriptionController::class, 'pay']);
