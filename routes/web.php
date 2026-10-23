<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Customer\OrderTrackingController;
use App\Http\Controllers\Customer\PublicMenuController;
use App\Http\Controllers\Customer\PublicMenuItemController;
use App\Http\Controllers\Customer\PublicPageController;
use App\Http\Controllers\Customer\PublicReservationController;
use App\Http\Controllers\Customer\QrCartShortcutController;
use App\Http\Controllers\Customer\TableOrderingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/storefront', HomeController::class)->name('storefront.index');

// Compatibility URLs for the imported resource views, backed by the Gilas menu domain.
Route::get('/shop', [PublicMenuController::class, 'index'])->name('shop.index');
Route::get('/products', [PublicMenuController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [PublicMenuItemController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('products.show');
Route::get('/cart', QrCartShortcutController::class)->name('cart.index');
Route::get('/checkout', QrCartShortcutController::class)->name('checkout.index');

Route::get('/menu', [PublicMenuController::class, 'index'])->name('menu.index');
Route::get('/menu/category/{slug}', [PublicMenuController::class, 'category'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('menu.category');
Route::get('/menu/item/{slug}', [PublicMenuItemController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('menu.item');

Route::get('/experience', [PublicPageController::class, 'show'])->defaults('page', 'experience')->name('public.experience');
Route::get('/reservation', [PublicPageController::class, 'show'])->defaults('page', 'reservation')->name('public.reservation');
Route::post('/reservation', [PublicReservationController::class, 'store'])->middleware('throttle:8,1')->name('public.reservations.store');
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

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
        Route::get('/{restaurant}/dashboard', AdminDashboardController::class)->name('admin.dashboard');
        Route::get('/restaurants/{restaurant}', AdminDashboardController::class)->name('admin.dashboard.overview');
        Route::post('/restaurants/{restaurant:slug}/payments/{payment}/cashier-paid', [PaymentController::class, 'markCashierPaid'])
            ->middleware('can:markCashierPaid,payment')
            ->name('admin.payments.cashier-paid');
    });
});
