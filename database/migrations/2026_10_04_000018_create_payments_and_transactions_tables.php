<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('amount');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('status')->default('pending');
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'status']);
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
    }
};
