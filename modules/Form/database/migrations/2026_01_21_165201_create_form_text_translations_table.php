<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('form_text_translations'))
        Schema::create('form_text_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_text_id')->constrained('form_texts')->onDelete('cascade');
            $table->string('locale', 10);
            $table->text('header_text')->nullable();
            $table->text('success_text')->nullable();
            $table->text('email_text')->nullable();
            $table->timestamps();

            $table->unique(['form_text_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_text_translations');
    }
};
