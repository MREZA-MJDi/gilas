<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(string $publicToken): View
    {
        $order = $this->findOrder($publicToken);

        $returnUrl = $order->order_type === OrderType::DineIn && request()->cookie('gilas_table_token')
            ? route('table.menu', request()->cookie('gilas_table_token'))
            : route('menu.index');

        return view('customer.order-waiting', [
            'order' => $order,
            'statusUrl' => route('customer.orders.status', $order->public_token),
            'returnUrl' => $returnUrl,
        ]);
    }

    public function status(string $publicToken): JsonResponse
    {
        $order = $this->findOrder($publicToken);

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'status_label' => $this->statusLabel($order->status),
                'total' => $order->total,
                'table' => $order->table?->number,
                'order_type' => $order->order_type->value,
            ],
        ]);
    }

    private function findOrder(string $publicToken): Order
    {
        return Order::query()
            ->where('public_token', $publicToken)
            ->with(['restaurant:id,name,currency', 'table:id,number'])
            ->firstOrFail();
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
