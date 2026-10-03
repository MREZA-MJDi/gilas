<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('min_order_amount')->default(0);
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedInteger('estimated_minutes')->default(45);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
