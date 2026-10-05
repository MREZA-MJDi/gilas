<?php

use AppHttpControllersAdminAuthController as AdminAuthController;
use AppHttpControllersAdminDashboardController as AdminDashboardController;
use AppHttpControllersCustomerOrderTrackingController;
use AppHttpControllersCustomerPublicMenuController;
use AppHttpControllersCustomerPublicMenuItemController;
use AppHttpControllersCustomerPublicPageController;
use AppHttpControllersCustomerTableOrderingController;
use AppHttpControllersHomeController;
use IlluminateSupportFacadesRoute;

Route::get('/', HomeController::class)->name('home');

Route::get('/menu', [PublicMenuController::class,'index'])->name('menu.index');
Route::get('/menu/category/{slug}', [PublicMenuController::class,'category'])->where('slug','[A-Za-z0-9_-]+')->name('menu.category');
Route::get('/menu/item/{slug}', [PublicMenuItemController::class,'show'])->where('slug','[A-Za-z0-9_-]+')->name('menu.item');

Route::get('/experience', [PublicPageController::class,'show'])->defaults('page','experience')->name('public.experience');
Route::get('/reservation', [PublicPageController::class,'show'])->defaults('page','reservation')->name('public.reservation');
Route::get('/story', [PublicPageController::class,'show'])->defaults('page','story')->name('public.story');
Route::get('/location', [PublicPageController::class,'show'])->defaults('page','location')->name('public.location');
Route::get('/club', [PublicPageController::class,'show'])->defaults('page','club')->name('public.club');

Route::get('/table/{token}', [TableOrderingController::class,'menu'])->where('token','[A-Za-z0-9]+')->name('table.menu');
Route::post('/table/{token}/orders', [TableOrderingController::class,'storeOrder'])->where('token','[A-Za-z0-9]+')->name('table.orders.store');
Route::get('/orders/{publicToken}', [OrderTrackingController::class,'show'])->where('publicToken','[A-Za-z0-9]+')->name('customer.orders.show');
Route::get('/orders/{publicToken}/status', [OrderTrackingController::class,'status'])->where('publicToken','[A-Za-z0-9]+')->name('customer.orders.status');

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login',[AdminAuthController::class,'create'])->name('admin.login');
        Route::post('/login',[AdminAuthController::class,'store'])->name('admin.login.store');
    });
    Route::middleware('auth')->group(function () {
        Route::post('/logout',[AdminAuthController::class,'destroy'])->name('admin.logout');
        Route::get('/{restaurant}/dashboard',AdminDashboardController::class)->name('admin.dashboard');
    });
});
