<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderStatusRequest;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    public function update(
        StoreOrderStatusRequest $request,
        Restaurant $restaurant,
        Order $order,
        OrderService $orders
    ): RedirectResponse|JsonResponse {
        abort_unless((int) $order->restaurant_id === (int) $restaurant->id, 404);

        $this->authorize('changeStatus', $order);
        $data = $request->validated();

        $updated = $orders->changeStatus(
            $order,
            OrderStatus::from($data['status']),
            $request->user(),
            $data['note'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'order_id' => $updated->id,
                    'status' => $updated->status->value,
                ],
            ]);
        }

        return back()->with('status', 'وضعیت سفارش به‌روزرسانی شد.');
    }
}
