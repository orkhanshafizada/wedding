<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table): void {
            if (! Schema::hasColumn('menus', 'show_in_sitemap')) {
                $table
                    ->boolean('show_in_sitemap')
                    ->default(true)
                    ->after('show_on_main_page')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table): void {
            if (Schema::hasColumn('menus', 'show_in_sitemap')) {
                $table->dropColumn('show_in_sitemap');
            }
        });
    }
};
