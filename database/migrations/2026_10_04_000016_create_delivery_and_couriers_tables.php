<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 40)->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('delivery_address_snapshot')->nullable();
            $table->text('customer_note')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['courier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('couriers');
    }
};
