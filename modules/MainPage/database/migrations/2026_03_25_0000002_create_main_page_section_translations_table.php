<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('main_page_section_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_page_section_id');
            $table->unsignedBigInteger('language_id');
            $table->string('title')->nullable();
            $table->timestamps();

            $table->unique(
                ['main_page_section_id', 'language_id'],
                'main_page_section_language_unique'
            );

            $table->foreign('main_page_section_id')
                ->references('id')
                ->on('main_page_sections')
                ->cascadeOnDelete();

            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_page_section_translations');
    }
};
