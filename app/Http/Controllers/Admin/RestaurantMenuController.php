<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\StoreMenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\ImageService;
use App\Services\RestaurantAccessService;
use App\Enums\RestaurantPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantMenuController extends Controller
{
    public function index(Restaurant $restaurant, RestaurantAccessService $access): JsonResponse
    {
        $this->assertCanManage($access, request()->user(), $restaurant);

        $categories = $restaurant->categories()
            ->with([
                'items' => fn ($query) => $query->orderBy('sort_order')->with([
                    'variants' => fn ($query) => $query->orderBy('sort_order'),
                    'options' => fn ($query) => $query->orderBy('sort_order')->with('values'),
                ]),
            ])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function storeCategory(
        StoreMenuCategoryRequest $request,
        Restaurant $restaurant,
        RestaurantAccessService $access,
        ImageService $images
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $data = $request->validated();
        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $images->store($request->file('image'), 'menu/categories');
        }

        $category = $restaurant->categories()->create($data);

        return response()->json(['data' => $category], 201);
    }

    public function updateCategory(
        StoreMenuCategoryRequest $request,
        Restaurant $restaurant,
        MenuCategory $category,
        RestaurantAccessService $access,
        ImageService $images
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertCategoryBelongsToRestaurant($category, $restaurant);

        $data = $request->validated();
        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $images->replace(
                $category->image_path,
                $request->file('image'),
                'menu/categories'
            );
        } elseif ($request->boolean('remove_image')) {
            $images->delete($category->image_path);
            $data['image_path'] = null;
        }

        $category->update($data);

        return response()->json(['data' => $category->fresh()]);
    }

    public function destroyCategory(
        Restaurant $restaurant,
        MenuCategory $category,
        RestaurantAccessService $access,
        ImageService $images
    ): JsonResponse {
        $this->assertCanManage($access, request()->user(), $restaurant);
        $this->assertCategoryBelongsToRestaurant($category, $restaurant);

        if ($category->items()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Move or delete this category’s menu items before deleting the category.',
            ]);
        }

        $images->delete($category->image_path);
        $category->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function storeItem(
        StoreMenuItemRequest $request,
        Restaurant $restaurant,
        RestaurantAccessService $access,
        ImageService $images
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $data = $request->validated();
        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $images->store($request->file('image'), 'menu/items');
        }

        $item = $restaurant->items()->create($data);

        return response()->json(['data' => $item->load('category')], 201);
    }

    public function updateItem(
        StoreMenuItemRequest $request,
        Restaurant $restaurant,
        MenuItem $item,
        RestaurantAccessService $access,
        ImageService $images
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);

        $data = $request->validated();
        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $images->replace(
                $item->image_path,
                $request->file('image'),
                'menu/items'
            );
        } elseif ($request->boolean('remove_image')) {
            $images->delete($item->image_path);
            $data['image_path'] = null;
        }

        $item->update($data);

        return response()->json(['data' => $item->fresh('category')]);
    }

    public function destroyItem(
        Restaurant $restaurant,
        MenuItem $item,
        RestaurantAccessService $access,
        ImageService $images
    ): JsonResponse {
        $this->assertCanManage($access, request()->user(), $restaurant);
        $this->assertItemBelongsToRestaurant($item, $restaurant);

        $images->delete($item->image_path);
        $item->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    private function assertCanManage(
        RestaurantAccessService $access,
        $user,
        Restaurant $restaurant
    ): void {
        abort_unless(
            $user && $access->can($user, $restaurant, RestaurantPermission::ManageMenu),
            403
        );
    }

    private function assertCategoryBelongsToRestaurant(MenuCategory $category, Restaurant $restaurant): void
    {
        abort_unless((int) $category->restaurant_id === (int) $restaurant->id, 404);
    }

    private function assertItemBelongsToRestaurant(MenuItem $item, Restaurant $restaurant): void
    {
        abort_unless((int) $item->restaurant_id === (int) $restaurant->id, 404);
    }
}
