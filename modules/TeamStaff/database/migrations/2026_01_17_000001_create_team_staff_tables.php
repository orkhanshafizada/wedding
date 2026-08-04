<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('team_staff'))
        Schema::create('team_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->index('menu_id');
            $table->json('name');
            $table->json('company')->nullable();
            $table->json('position')->nullable();
            $table->json('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('profile_picture')->nullable();
            $table->json('social_networks')->nullable();
            $table->json('files')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_staff');
    }
};
