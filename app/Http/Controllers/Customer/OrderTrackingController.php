<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(string $publicToken): View
    {
        $order = Order::query()
            ->where('public_token', $publicToken)
            ->with(['restaurant:id,name,currency', 'table:id,number'])
            ->firstOrFail();

        return view('customer.order-waiting', [
            'order' => $order,
            'statusUrl' => route('customer.orders.status', $order->public_token),
        ]);
    }

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
