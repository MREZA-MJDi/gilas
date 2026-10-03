<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('min_select')->default(0);
            $table->unsignedTinyInteger('max_select')->default(1);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['menu_item_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_options');
    }
};
