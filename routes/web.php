<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\OrderTrackingController;
use App\Http\Controllers\Customer\PublicMenuController;
use App\Http\Controllers\Customer\PublicMenuItemController;
use App\Http\Controllers\Customer\PublicPageController;
use App\Http\Controllers\Customer\TableOrderingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/menu', [PublicMenuController::class, 'index'])->name('menu.index');
Route::get('/menu/category/{slug}', [PublicMenuController::class, 'category'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('menu.category');
Route::get('/menu/item/{slug}', [PublicMenuItemController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('menu.item');

Route::get('/experience', [PublicPageController::class, 'show'])->defaults('page', 'experience')->name('public.experience');
Route::get('/reservation', [PublicPageController::class, 'show'])->defaults('page', 'reservation')->name('public.reservation');
Route::get('/story', [PublicPageController::class, 'show'])->defaults('page', 'story')->name('public.story');
Route::get('/location', [PublicPageController::class, 'show'])->defaults('page', 'location')->name('public.location');
Route::get('/club', [PublicPageController::class, 'show'])->defaults('page', 'club')->name('public.club');

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

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin/{restaurant:slug}', DashboardController::class)
        ->name('admin.dashboard');
});
