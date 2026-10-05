<?php

namespace AppHttpControllersAdmin;

use AppEnumsOrderStatus;
use AppHttpControllersController;
use AppModelsRestaurant;
use IlluminateSupportCarbon;
use IlluminateSupportFacadesAuth;
use IlluminateViewView;

class DashboardController extends Controller
{
    public function __invoke(Restaurant $restaurant): View
    {
        $this->authorize('view', $restaurant);
        $today = Carbon::today();

        $openStatuses = array_map(
            static fn (OrderStatus $status) => $status->value,
            [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Preparing,
                OrderStatus::Ready,
                OrderStatus::Served,
                OrderStatus::OutForDelivery,
                OrderStatus::Delivered,
            ]
        );

        $ordersToday = $restaurant->orders()->whereDate('created_at', $today)->count();
        $revenueToday = (int) $restaurant->orders()->whereDate('created_at', $today)->where('status','!=',OrderStatus::Cancelled->value)->sum('total');
        $paidToday = (int) $restaurant->orders()->whereDate('created_at', $today)->whereHas('payment', fn($q) => $q->where('status','paid'))->sum('total');
        $openOrders = $restaurant->orders()->whereIn('status',$openStatuses)->count();
        $activeTables = $restaurant->orders()->whereIn('status',$openStatuses)->whereNotNull('restaurant_table_id')->distinct('restaurant_table_id')->count('restaurant_table_id');
        $menuCount = $restaurant->items()->where('is_active',true)->where('is_available',true)->count();
        $categoryCount = $restaurant->categories()->where('is_active',true)->count();
        $reservationsToday = $restaurant->reservations()->whereDate('reservation_date',$today)->whereIn('status',['pending','confirmed','seated'])->count();

        $recentOrders = $restaurant->orders()->with(['customer:id,name','table:id,number','items:id,order_id,quantity'])->latest()->limit(8)->get();

        return view('admin.dashboard', [
            'restaurant'=>$restaurant,
            'user'=>Auth::user(),
            'metrics'=>compact('ordersToday','revenueToday','paidToday','openOrders','activeTables','menuCount','categoryCount','reservationsToday'),
            'recentOrders'=>$recentOrders,
        ]);
    }
}
