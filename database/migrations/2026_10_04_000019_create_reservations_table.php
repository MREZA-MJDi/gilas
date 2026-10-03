<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_table_id')->nullable()->constrained()->nullOnDelete();
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->unsignedTinyInteger('guest_count');
            $table->string('status')->default('pending');
            $table->text('customer_note')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'reservation_date', 'status']);
            $table->index(['restaurant_table_id', 'reservation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
