<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('grid_media'))
        Schema::create('grid_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grid_id')->index();

            $table->string('type', 16)->default('image')->index(); // image|file
            $table->string('path');
            $table->string('original_name')->nullable();

            $table->boolean('is_main')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->timestamps();

            $table->foreign('grid_id')
                ->references('id')
                ->on('grids')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_media');
    }
};
