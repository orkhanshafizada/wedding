<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('menus'))
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menus')
                ->nullOnDelete();

            $table->string('type', 50)->index();
            $table->string('view_type', 50)->default('default')->index();

            $table->boolean('status')->default(false);
            $table->boolean('show_on_main_page')->default(false);
            $table->boolean('in_header')->default(false);
            $table->boolean('in_footer')->default(false);
            $table->string('main_image', 1000)->nullable();
            $table->string('icon', 1000)->nullable();
            $table->string('icon_image', 1000)->nullable();
            $table->string('text_color', 20)->nullable();
            $table->string('bg_color', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->timestamps();
        });

        if (!Schema::hasTable('menu_translations'))
        Schema::create('menu_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('menus')
                ->cascadeOnDelete();

            $table->string('locale', 5)->index();

            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('link')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->unique(['menu_id', 'locale']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_translations');
        Schema::dropIfExists('menus');
    }
};
