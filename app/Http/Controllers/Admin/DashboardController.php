<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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

        $ordersToday = $restaurant->orders()->whereDate('created_at', $today)->count();

        $revenueToday = (int) $restaurant->orders()
            ->whereDate('created_at', $today)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->sum('total');

        $paidToday = (int) $restaurant->orders()
            ->whereDate('created_at', $today)
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->sum('total');

        $openOrders = $restaurant->orders()->whereIn('status', $openStatuses)->count();

        $occupiedTableIds = $restaurant->orders()
            ->whereIn('status', $openStatuses)
            ->whereNotNull('restaurant_table_id')
            ->pluck('restaurant_table_id')
            ->unique()
            ->values();

        $activeTables = $occupiedTableIds->count();

        $tables = $restaurant->tables()
            ->with(['qrCode'])
            ->where('is_active', true)
            ->orderBy('floor')
            ->orderBy('zone')
            ->orderBy('number')
            ->get();

        $menuCount = $restaurant->items()
            ->where('is_active', true)
            ->where('is_available', true)
            ->count();

        $categoryCount = $restaurant->categories()
            ->where('is_active', true)
            ->count();

        $reservationsToday = $restaurant->reservations()
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->count();

        $openDeliveries = $restaurant->orders()
            ->where('order_type', 'delivery')
            ->whereHas('delivery', fn ($query) => $query->whereIn('status', [
                'pending', 'assigned', 'dispatched', 'picked_up',
            ]))
            ->count();

        $availableCouriers = $restaurant->couriers()
            ->whereIn('status', ['available', 'online'])
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
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->get(['created_at', 'total']);

        $dailySales = collect(range(0, 6))->map(function (int $offset) use ($start, $dailyRows): array {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();

            $rows = $dailyRows->filter(
                fn ($row) => $row->created_at->toDateString() === $key
            );

            return [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('j M'),
                'orders' => $rows->count(),
                'revenue' => (int) $rows->sum('total'),
            ];
        });

        $user = Auth::user();

        return view('admin.dashboard', [
            'restaurant' => $restaurant,
            'user' => $user,
            'metrics' => compact(
                'ordersToday',
                'revenueToday',
                'paidToday',
                'openOrders',
                'activeTables',
                'menuCount',
                'categoryCount',
                'reservationsToday',
                'openDeliveries',
                'availableCouriers',
            ),
            'tables' => $tables,
            'occupiedTableIds' => $occupiedTableIds->map(fn ($id) => (int) $id)->all(),
            'recentOrders' => $recentOrders,
            'kitchenQueue' => $kitchenQueue,
            'dailySales' => $dailySales,
        ]);
    }
}
