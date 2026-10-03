<?php

namespace App\Enums;

enum RestaurantPermission: string
{
    case ViewDashboard = 'dashboard.view';
    case ViewOrders = 'orders.view';
    case CreateOrders = 'orders.create';
    case ChangeOrderStatus = 'orders.status';
    case ManagePayments = 'payments.manage';
    case ManageMenu = 'menu.manage';
    case ViewMenu = 'menu.view';
    case ManageTables = 'tables.manage';
    case ViewTables = 'tables.view';
    case ManageDelivery = 'delivery.manage';
    case ViewDelivery = 'delivery.view';
    case ManageReservations = 'reservations.manage';
    case ViewReports = 'reports.view';
    case ManageStaff = 'staff.manage';
    case ManageSettings = 'settings.manage';
}
