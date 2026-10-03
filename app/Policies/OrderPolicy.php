<?php

namespace App\Policies;

use App\Enums\RestaurantPermission;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\RestaurantAccessService;

class OrderPolicy
{
    public function __construct(private readonly RestaurantAccessService $access) {}

    public function viewAny(User $user, Restaurant $restaurant): bool
    {
        return $this->access->can($user, $restaurant, RestaurantPermission::ViewOrders);
    }

    public function create(User $user, Restaurant $restaurant): bool
    {
        return $this->access->can($user, $restaurant, RestaurantPermission::CreateOrders);
    }

    public function changeStatus(User $user, Order $order): bool
    {
        $restaurant = $order->restaurant;

        return $restaurant !== null
            && $this->access->can($user, $restaurant, RestaurantPermission::ChangeOrderStatus);
    }
}
