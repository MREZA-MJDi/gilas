<?php

use App\Http\Controllers\Customer\OrderTrackingController;
use App\Http\Controllers\Customer\TableOrderingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/table/{token}', [TableOrderingController::class, 'menu'])
    ->where('token', '[A-Za-z0-9]+')
    ->name('table.menu');

Route::post('/table/{token}/orders', [TableOrderingController::class, 'storeOrder'])
    ->where('token', '[A-Za-z0-9]+')
    ->name('table.orders.store');

Route::get('/orders/{publicToken}', [OrderTrackingController::class, 'show'])
    ->where('publicToken', '[A-Za-z0-9]+')
    ->name('customer.orders.show');

Route::get('/orders/{publicToken}/status', [OrderTrackingController::class, 'status'])
    ->where('publicToken', '[A-Za-z0-9]+')
    ->name('customer.orders.status');
