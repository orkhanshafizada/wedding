<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 191);
            $table->string('locale', 20); // e.g., 'en', 'az', 'tr-TR'
            $table->text('value')->nullable();
            $table->enum('status', ['Draft', 'Translated'])->default('Draft');
            $table->json('sources')->nullable(); // where the key was found (files)
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['key', 'locale']);
            $table->index(['locale', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
