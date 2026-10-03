<?php

namespace App\Providers;

use App\Services\ImageService;
use App\Services\MenuCatalogService;
use App\Services\MenuPricingService;
use App\Services\OrderService;
use App\Services\RestaurantService;
use App\Services\TableQrCodeService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ImageService::class);
        $this->app->scoped(MenuCatalogService::class);
        $this->app->scoped(MenuPricingService::class);
        $this->app->scoped(OrderService::class);
        $this->app->scoped(RestaurantService::class);
        $this->app->scoped(TableQrCodeService::class);
    }

    public function boot(): void
    {
        //
    }
}
