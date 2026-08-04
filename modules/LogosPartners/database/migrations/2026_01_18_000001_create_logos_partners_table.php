<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('logos_partners'))
        Schema::create('logos_partners', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('menu_id')->nullable();
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->index('menu_id');

            $table->json('name');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->json('link')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
