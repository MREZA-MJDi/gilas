<?php

namespace App\Services;

use App\Enums\RestaurantPermission;
use App\Enums\RestaurantUserRole;
use App\Models\Restaurant;
use App\Models\User;

class RestaurantAccessService
{
    public function can(User $user, Restaurant $restaurant, RestaurantPermission|string $permission): bool
    {
        $permission = $permission instanceof RestaurantPermission
            ? $permission
            : RestaurantPermission::from($permission);

        $membership = $user->restaurants()
            ->where('restaurants.id', $restaurant->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$membership) {
            return false;
        }

        $role = RestaurantUserRole::tryFrom($membership->pivot->role);

        return $role !== null
            && in_array($permission, $this->permissionsFor($role), true);
    }

    /** @return array<RestaurantPermission> */
    public function permissionsFor(RestaurantUserRole $role): array
    {
        return match ($role) {
            RestaurantUserRole::Owner => RestaurantPermission::cases(),
            RestaurantUserRole::Manager => array_values(array_diff(
                RestaurantPermission::cases(),
                []
            )),
            RestaurantUserRole::Waiter => [
                RestaurantPermission::ViewOrders,
                RestaurantPermission::CreateOrders,
                RestaurantPermission::ChangeOrderStatus,
                RestaurantPermission::ViewMenu,
                RestaurantPermission::ViewTables,
            ],
            RestaurantUserRole::Kitchen => [
                RestaurantPermission::ViewOrders,
                RestaurantPermission::ChangeOrderStatus,
                RestaurantPermission::ViewMenu,
            ],
            RestaurantUserRole::Cashier => [
                RestaurantPermission::ViewDashboard,
                RestaurantPermission::ViewOrders,
                RestaurantPermission::ChangeOrderStatus,
                RestaurantPermission::ManagePayments,
                RestaurantPermission::ViewReports,
            ],
            RestaurantUserRole::Courier => [
                RestaurantPermission::ViewOrders,
                RestaurantPermission::ViewDelivery,
                RestaurantPermission::ManageDelivery,
            ],
            RestaurantUserRole::Staff => [
                RestaurantPermission::ViewOrders,
                RestaurantPermission::ViewMenu,
            ],
        };
    }
}
