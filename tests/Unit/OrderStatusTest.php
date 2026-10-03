<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_order_statuses_are_stable_api_values(): void
    {
        $this->assertSame('pending', OrderStatus::Pending->value);
        $this->assertSame('confirmed', OrderStatus::Confirmed->value);
        $this->assertSame('preparing', OrderStatus::Preparing->value);
        $this->assertSame('ready', OrderStatus::Ready->value);
        $this->assertSame('served', OrderStatus::Served->value);
        $this->assertSame('out_for_delivery', OrderStatus::OutForDelivery->value);
        $this->assertSame('delivered', OrderStatus::Delivered->value);
        $this->assertSame('completed', OrderStatus::Completed->value);
        $this->assertSame('cancelled', OrderStatus::Cancelled->value);
    }

    public function test_order_types_are_stable_api_values(): void
    {
        $this->assertSame('dine_in', OrderType::DineIn->value);
        $this->assertSame('pickup', OrderType::Pickup->value);
        $this->assertSame('delivery', OrderType::Delivery->value);
    }
}
