<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->boolean('ordering_enabled')->default(true);
            $table->boolean('dine_in_enabled')->default(true);
            $table->boolean('pickup_enabled')->default(true);
            $table->boolean('delivery_enabled')->default(true);
            $table->boolean('reservation_enabled')->default(true);
            $table->unsignedInteger('min_order_amount')->default(0);
            $table->unsignedInteger('default_delivery_minutes')->default(45);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('service_charge_percent', 5, 2)->default(0);
            $table->timestamps();

            $table->unique('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_settings');
    }
};
