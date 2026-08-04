<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_included_items', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('menus')
                ->cascadeOnDelete();

            $table->string('included_type', 50);
            $table->unsignedBigInteger('included_id');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['menu_id', 'included_type', 'included_id'], 'menu_included_items_unique');
            $table->index(['menu_id', 'sort_order'], 'menu_included_items_menu_sort_index');
            $table->index(['included_type', 'included_id'], 'menu_included_items_type_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_included_items');
    }
};
