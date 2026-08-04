<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('menu_content_files'))
        Schema::create('menu_content_files', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('menu_content_id')->index();

            $table->string('path');
            $table->string('original_name', 255);
            $table->string('extension', 20)->index();

            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size')->nullable();

            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->timestamps();

            $table->foreign('menu_content_id')
                ->references('id')->on('menu_contents')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_content_files');
    }
};
