<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('grids'))
        Schema::create('grids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id')->index();

            $table->timestamp('datetime1')->nullable();
            $table->timestamp('datetime2')->nullable();
            $table->string('banner')->nullable();

            $table->json('name');
            $table->json('slug');
            $table->json('content');
            $table->json('location_or_group');
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('menu_id')
                ->references('id')
                ->on('menus')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grids');
    }
};
