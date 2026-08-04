<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('form_label_translations'))
        Schema::create('form_label_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_label_id')->constrained('form_labels')->onDelete('cascade');
            $table->string('locale', 10)->index();
            $table->string('name');
            $table->text('information')->nullable();
            $table->timestamps();

            $table->unique(['form_label_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_label_translations');
    }
};
