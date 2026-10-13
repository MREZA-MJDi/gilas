<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RestaurantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuOptionRequest;
use App\Http\Requests\StoreMenuOptionValueRequest;
use App\Http\Requests\StoreMenuVariantRequest;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionValue;
use App\Models\MenuItemVariant;
use App\Models\Restaurant;
use App\Services\RestaurantAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MenuConfigurationController extends Controller
{
    public function storeVariant(
        StoreMenuVariantRequest $request,
        Restaurant $restaurant,
        MenuItem $item,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);

        return response()->json([
            'data' => $item->variants()->create($request->validated()),
        ], 201);
    }

    public function updateVariant(
        StoreMenuVariantRequest $request,
        Restaurant $restaurant,
        MenuItem $item,
        MenuItemVariant $variant,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        abort_unless((int) $variant->menu_item_id === (int) $item->id, 404);

        $variant->update($request->validated());

        return response()->json(['data' => $variant->fresh()]);
    }

    public function destroyVariant(
        Restaurant $restaurant,
        MenuItem $item,
        MenuItemVariant $variant,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, request()->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        abort_unless((int) $variant->menu_item_id === (int) $item->id, 404);

        $variant->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function storeOption(
        StoreMenuOptionRequest $request,
        Restaurant $restaurant,
        MenuItem $item,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        $this->assertOptionRange($request->validated());

        return response()->json([
            'data' => $item->options()->create($request->validated()),
        ], 201);
    }

    public function updateOption(
        StoreMenuOptionRequest $request,
        Restaurant $restaurant,
        MenuItem $item,
        MenuItemOption $option,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        abort_unless((int) $option->menu_item_id === (int) $item->id, 404);
        $this->assertOptionRange($request->validated());

        $option->update($request->validated());

        return response()->json(['data' => $option->fresh()]);
    }

    public function destroyOption(
        Restaurant $restaurant,
        MenuItem $item,
        MenuItemOption $option,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, request()->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        abort_unless((int) $option->menu_item_id === (int) $item->id, 404);

        $option->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function storeOptionValue(
        StoreMenuOptionValueRequest $request,
        Restaurant $restaurant,
        MenuItem $item,
        MenuItemOption $option,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        abort_unless((int) $option->menu_item_id === (int) $item->id, 404);

        return response()->json([
            'data' => $option->values()->create($request->validated()),
        ], 201);
    }

    public function updateOptionValue(
        StoreMenuOptionValueRequest $request,
        Restaurant $restaurant,
        MenuItem $item,
        MenuItemOption $option,
        MenuItemOptionValue $value,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        abort_unless((int) $option->menu_item_id === (int) $item->id, 404);
        abort_unless((int) $value->menu_item_option_id === (int) $option->id, 404);

        $value->update($request->validated());

        return response()->json(['data' => $value->fresh()]);
    }

    public function destroyOptionValue(
        Restaurant $restaurant,
        MenuItem $item,
        MenuItemOption $option,
        MenuItemOptionValue $value,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, request()->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);
        abort_unless((int) $option->menu_item_id === (int) $item->id, 404);
        abort_unless((int) $value->menu_item_option_id === (int) $option->id, 404);

        $value->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    private function assertOptionRange(array $data): void
    {
        if ((int) $data['min_select'] > (int) $data['max_select']) {
            throw ValidationException::withMessages([
                'max_select' => 'max_select must be greater than or equal to min_select.',
            ]);
        }
    }

    private function assertCanManage(RestaurantAccessService $access, $user, Restaurant $restaurant): void
    {
        abort_unless(
            $user && $access->can($user, $restaurant, RestaurantPermission::ManageMenu),
            403
        );
    }

    private function assertItemBelongsToRestaurant(MenuItem $item, Restaurant $restaurant): void
    {
        abort_unless((int) $item->restaurant_id === (int) $restaurant->id, 404);
    }
}
