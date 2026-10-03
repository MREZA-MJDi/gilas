<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_table_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['restaurant_table_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_qr_codes');
    }
};
