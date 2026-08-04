<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('grids_related_products')) {
            Schema::create('grids_related_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grid_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('product_variation_id')->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();

                $table->unique(['grid_id', 'product_id']);
                $table->unique(['grid_id', 'product_variation_id']);

                $table->foreign('grid_id')
                    ->references('id')
                    ->on('grids')
                    ->cascadeOnDelete();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();

                $table->foreign('product_variation_id')
                    ->references('id')
                    ->on('product_variations')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grids_related_products');
    }
};
