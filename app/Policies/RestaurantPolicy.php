<?php

namespace App\Policies;

use App\Enums\RestaurantPermission;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\RestaurantAccessService;

class RestaurantPolicy
{
    public function __construct(private readonly RestaurantAccessService $access) {}

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $this->access->can($user, $restaurant, RestaurantPermission::ViewDashboard);
    }

    public function manageStaff(User $user, Restaurant $restaurant): bool
    {
        return $this->access->can($user, $restaurant, RestaurantPermission::ManageStaff);
    }

    public function manageSettings(User $user, Restaurant $restaurant): bool
    {
        return $this->access->can($user, $restaurant, RestaurantPermission::ManageSettings);
    }
}
