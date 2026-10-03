<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['restaurant_id', 'slug']);
            $table->index(['menu_category_id', 'sort_order']);
            $table->index(['restaurant_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
