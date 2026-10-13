<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CourierManagementController;
use App\Http\Controllers\Admin\DeliveryOperationsController;
use App\Http\Controllers\Admin\MenuConfigurationController;
use App\Http\Controllers\Admin\RestaurantMenuController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReservationStatusController;
use App\Http\Controllers\Customer\OrderTrackingController;
use App\Http\Controllers\Customer\PublicOrderController;
use App\Http\Controllers\Customer\ReservationController;
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
Route::post('/orders', [PublicOrderController::class, 'store'])->name('customer.orders.store');

Route::get('/experience', [PublicPageController::class, 'show'])->defaults('page', 'experience')->name('public.experience');
Route::get('/reservation', [PublicPageController::class, 'show'])->defaults('page', 'reservation')->name('public.reservation');
Route::post('/reservation', [ReservationController::class, 'store'])->name('public.reservations.store');
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

        // Preserve the established dashboard URL so existing bookmarks keep working.
        Route::get('/{restaurant}/dashboard', AdminDashboardController::class)->name('admin.dashboard');

        // Keep the newer, resource-oriented URL as an alias.
        Route::get('/restaurants/{restaurant}', AdminDashboardController::class)->name('admin.dashboard.overview');

        Route::get('/restaurants/{restaurant:slug}/menu', [RestaurantMenuController::class, 'index'])
            ->name('admin.menu.index');
        Route::post('/restaurants/{restaurant:slug}/menu/categories', [RestaurantMenuController::class, 'storeCategory'])
            ->name('admin.menu.categories.store');
        Route::patch('/restaurants/{restaurant:slug}/menu/categories/{category}', [RestaurantMenuController::class, 'updateCategory'])
            ->name('admin.menu.categories.update');
        Route::delete('/restaurants/{restaurant:slug}/menu/categories/{category}', [RestaurantMenuController::class, 'destroyCategory'])
            ->name('admin.menu.categories.destroy');
        Route::post('/restaurants/{restaurant:slug}/menu/items', [RestaurantMenuController::class, 'storeItem'])
            ->name('admin.menu.items.store');
        Route::patch('/restaurants/{restaurant:slug}/menu/items/{item}', [RestaurantMenuController::class, 'updateItem'])
            ->name('admin.menu.items.update');
        Route::delete('/restaurants/{restaurant:slug}/menu/items/{item}', [RestaurantMenuController::class, 'destroyItem'])
            ->name('admin.menu.items.destroy');

        Route::post('/restaurants/{restaurant:slug}/menu/items/{item}/variants', [MenuConfigurationController::class, 'storeVariant'])
            ->name('admin.menu.variants.store');
        Route::patch('/restaurants/{restaurant:slug}/menu/items/{item}/variants/{variant}', [MenuConfigurationController::class, 'updateVariant'])
            ->name('admin.menu.variants.update');
        Route::delete('/restaurants/{restaurant:slug}/menu/items/{item}/variants/{variant}', [MenuConfigurationController::class, 'destroyVariant'])
            ->name('admin.menu.variants.destroy');
        Route::post('/restaurants/{restaurant:slug}/menu/items/{item}/options', [MenuConfigurationController::class, 'storeOption'])
            ->name('admin.menu.options.store');
        Route::patch('/restaurants/{restaurant:slug}/menu/items/{item}/options/{option}', [MenuConfigurationController::class, 'updateOption'])
            ->name('admin.menu.options.update');
        Route::delete('/restaurants/{restaurant:slug}/menu/items/{item}/options/{option}', [MenuConfigurationController::class, 'destroyOption'])
            ->name('admin.menu.options.destroy');
        Route::post('/restaurants/{restaurant:slug}/menu/items/{item}/options/{option}/values', [MenuConfigurationController::class, 'storeOptionValue'])
            ->name('admin.menu.option-values.store');
        Route::patch('/restaurants/{restaurant:slug}/menu/items/{item}/options/{option}/values/{value}', [MenuConfigurationController::class, 'updateOptionValue'])
            ->name('admin.menu.option-values.update');
        Route::delete('/restaurants/{restaurant:slug}/menu/items/{item}/options/{option}/values/{value}', [MenuConfigurationController::class, 'destroyOptionValue'])
            ->name('admin.menu.option-values.destroy');

        Route::get('/restaurants/{restaurant:slug}/couriers', [CourierManagementController::class, 'index'])
            ->name('admin.couriers.index');
        Route::post('/restaurants/{restaurant:slug}/couriers', [CourierManagementController::class, 'store'])
            ->name('admin.couriers.store');
        Route::patch('/restaurants/{restaurant:slug}/couriers/{courier}', [CourierManagementController::class, 'update'])
            ->name('admin.couriers.update');
        Route::delete('/restaurants/{restaurant:slug}/couriers/{courier}', [CourierManagementController::class, 'destroy'])
            ->name('admin.couriers.destroy');

        Route::get('/restaurants/{restaurant:slug}/deliveries', [DeliveryOperationsController::class, 'index'])
            ->name('admin.deliveries.index');
        Route::patch('/restaurants/{restaurant:slug}/deliveries/{delivery}/status', [DeliveryOperationsController::class, 'update'])
            ->name('admin.deliveries.status');

        Route::patch('/restaurants/{restaurant:slug}/orders/{order}/status', [OrderStatusController::class, 'update'])
            ->name('admin.orders.status');
        Route::patch('/restaurants/{restaurant:slug}/reservations/{reservation}/status', [ReservationStatusController::class, 'update'])
            ->name('admin.reservations.status');

        Route::post('/restaurants/{restaurant:slug}/payments/{payment}/cashier-paid', [PaymentController::class, 'markCashierPaid'])
            ->middleware('can:markCashierPaid,payment')
            ->name('admin.payments.cashier-paid');
    });
});
