<?php

namespace AppHttpControllersCustomer;

use AppEnumsOrderStatus;
use AppHttpControllersController;
use AppModelsOrder;
use Illuminate\Http\JsonResponse;

class OrderTrackingController extends Controller
{
    public function status(string $publicToken): JsonResponse
    {
        $order = Order::query()
            ->where('public_token', $publicToken)
            ->with(['restaurant:id,name,currency', 'table:id,number'])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'status_label' => $this->statusLabel($order->status),
                'total' => $order->total,
                'table' => $order->table?->number,
            ],
        ]);
    }

    private function statusLabel(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Pending => 'در صف بررسی',
            OrderStatus::Confirmed => 'سفارش تأیید شد',
            OrderStatus::Preparing => 'در حال آماده‌سازی',
            OrderStatus::Ready => 'آماده شد',
            OrderStatus::Served => 'سرو شد',
            OrderStatus::OutForDelivery => 'در مسیر',
            OrderStatus::Delivered => 'تحویل شد',
            OrderStatus::Completed => 'تکمیل شد',
            OrderStatus::Cancelled => 'لغو شد',
        };
    }
}
