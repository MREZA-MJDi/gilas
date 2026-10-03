<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableOrderRequest;
use App\Services\MenuCatalogService;
use App\Services\OrderService;
use App\Services\TableQrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TableOrderingController extends Controller
{
    public function __construct(
        private readonly TableQrCodeService $qrCodes,
        private readonly MenuCatalogService $catalog,
        private readonly OrderService $orders,
    ) {
    }

    public function menu(string $token): View
    {
        $qr = $this->qrCodes->resolveOrFail($token);
        $restaurant = $qr->table->restaurant;

        $menu = $this->catalog->forRestaurant($restaurant);

        return view('customer.table-menu', [
            'restaurant' => $restaurant,
            'table' => $qr->table,
            'qr' => $qr,
            'menu' => $menu,
        ]);
    }

    public function storeOrder(StoreTableOrderRequest $request, string $token): JsonResponse
    {
        $qr = $this->qrCodes->resolveOrFail($token);

        $order = $this->orders->create($qr->table->restaurant, [
            ...$request->validated(),
            'restaurant_table_id' => $qr->table->id,
            'order_type' => 'dine_in',
            'idempotency_key' => $request->idempotencyKey(),
        ]);

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'total' => $order->total,
                'public_token' => $order->public_token,
                'tracking_url' => route('customer.orders.show', $order->public_token),
            ],
        ], 201);
    }

    public function invalid(): Response
    {
        return response()->noContent(410);
    }
}
