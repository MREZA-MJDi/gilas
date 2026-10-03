<?php

namespace App\Policies;

use App\Enums\RestaurantPermission;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\RestaurantAccessService;

class RestaurantTablePolicy
{
    public function __construct(private readonly RestaurantAccessService $access) {}

    public function viewAny(User $user, Restaurant $restaurant): bool
    {
        return $this->access->can($user, $restaurant, RestaurantPermission::ViewTables);
    }

    public function manage(User $user, Restaurant $restaurant): bool
    {
        return $this->access->can($user, $restaurant, RestaurantPermission::ManageTables);
    }

    public function regenerateQr(User $user, RestaurantTable $table): bool
    {
        return $this->access->can($user, $table->restaurant, RestaurantPermission::ManageTables);
    }
}
