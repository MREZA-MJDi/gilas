<?php

namespace App\Providers;

use App\Services\ImageService;
use App\Services\OrderService;
use App\Services\RestaurantService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ImageService::class);
        $this->app->scoped(OrderService::class);
        $this->app->scoped(RestaurantService::class);
    }

    public function boot(): void
    {
        //
    }
}
