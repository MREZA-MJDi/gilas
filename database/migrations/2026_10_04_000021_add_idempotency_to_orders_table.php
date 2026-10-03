<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->after('order_number');
            $table->string('idempotency_hash', 64)->nullable()->after('idempotency_key');

            $table->unique(['restaurant_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_restaurant_id_idempotency_key_unique');
            $table->dropColumn(['idempotency_key', 'idempotency_hash']);
        });
    }
};
