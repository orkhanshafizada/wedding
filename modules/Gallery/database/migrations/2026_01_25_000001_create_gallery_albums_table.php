<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gallery_albums'))
        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->boolean('show_album')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('cover_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        if (!Schema::hasTable('gallery_album_translations'))
        Schema::create('gallery_album_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_album_id')->constrained('gallery_albums')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('name');
            $table->timestamps();

            $table->unique(['gallery_album_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_album_translations');
        Schema::dropIfExists('gallery_albums');
    }
};
