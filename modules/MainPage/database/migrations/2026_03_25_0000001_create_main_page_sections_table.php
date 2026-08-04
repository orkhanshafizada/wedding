<?php

use App\Enums\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('main_page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 100);
            $table->string('source_reference')->nullable();
            $table->string('menu_type', 100)->nullable();
            $table->string('menu_view_type', 100)->nullable();
            $table->unsignedInteger('limit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 50)->default(StatusEnum::ACTIVE);
            $table->timestamps();

            $table->index(['source_type', 'status']);
            $table->index(['menu_type', 'menu_view_type']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_page_sections');
    }
};
