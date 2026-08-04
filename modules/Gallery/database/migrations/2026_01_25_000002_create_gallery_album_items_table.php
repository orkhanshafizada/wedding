<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gallery_album_items'))
        Schema::create('gallery_album_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_album_id')->constrained('gallery_albums')->onDelete('cascade');
            $table->enum('type', ['photo', 'video', 'file'])->default('photo');
            $table->string('file_path')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('publication')->default(false)->comment('For PDF files');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        if (!Schema::hasTable('gallery_album_item_translations'))
        Schema::create('gallery_album_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_album_item_id')->constrained('gallery_album_items')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['gallery_album_item_id', 'locale'], 'gallery_item_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_album_item_translations');
        Schema::dropIfExists('gallery_album_items');
    }
};
