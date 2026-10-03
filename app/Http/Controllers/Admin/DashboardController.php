<?php

namespace AppHttpControllersAdmin;

use AppEnumsOrderStatus;
use AppHttpControllersController;
use AppModelsRestaurant;
use IlluminateSupportCarbon;
use IlluminateViewView;

class DashboardController extends Controller
{
    public function __invoke(Restaurant $restaurant): View
    {
        $this->authorize('view', $restaurant);

        $today = Carbon::today();
        $openStatuses = [
            OrderStatus::Pending->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Preparing->value,
            OrderStatus::Ready->value,
            OrderStatus::Served->value,
            OrderStatus::OutForDelivery->value,
            OrderStatus::Delivered->value,
        ];

        $ordersToday = $restaurant->orders()
            ->whereDate('created_at', $today)
            ->count();

        $revenueToday = (int) $restaurant->orders()
            ->whereDate('created_at', $today)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->sum('total');

        $openOrders = $restaurant->orders()
            ->whereIn('status', $openStatuses)
            ->count();

        $activeTables = $restaurant->orders()
            ->whereIn('status', $openStatuses)
            ->whereNotNull('restaurant_table_id')
            ->distinct('restaurant_table_id')
            ->count('restaurant_table_id');

        $menuCount = $restaurant->items()
            ->where('is_active', true)
            ->where('is_available', true)
            ->count();

        $reservationsToday = $restaurant->reservations()
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->count();

        $recentOrders = $restaurant->orders()
            ->with(['customer', 'table', 'items'])
            ->latest()
            ->limit(8)
            ->get();

        $kitchenQueue = $restaurant->orders()
            ->with(['table', 'items'])
            ->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Preparing,
                OrderStatus::Ready,
            ])
            ->latest()
            ->limit(6)
            ->get();

        $start = $today->copy()->subDays(6);
        $dailyRows = $restaurant->orders()
            ->whereBetween('created_at', [$start->startOfDay(), $today->copy()->endOfDay()])
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->get(['created_at', 'total']);

        $days = collect(range(0, 6))->mapWithKeys(function (int $offset) use ($start): array {
            $date = $start->copy()->addDays($offset);

            return [
                $date->toDateString() => [
                    'label' => $date->translatedFormat('D'),
                    'date' => $date->format('j M'),
                    'orders' => 0,
                    'revenue' => 0,
                ],
            ];
        });

        foreach ($dailyRows as $row) {
            $key = $row->created_at->toDateString();

            if ($days->has($key)) {
                $day = $days->get($key);
                $day['orders']++;
                $day['revenue'] += (int) $row->total;
                $days->put($key, $day);
            }
        }

        return view('admin.dashboard', [
            'restaurant' => $restaurant,
            'metrics' => compact(
                'ordersToday',
                'revenueToday',
                'openOrders',
                'activeTables',
                'menuCount',
                'reservationsToday',
            ),
            'recentOrders' => $recentOrders,
            'kitchenQueue' => $kitchenQueue,
            'dailySales' => $days->values(),
        ]);
    }
}
