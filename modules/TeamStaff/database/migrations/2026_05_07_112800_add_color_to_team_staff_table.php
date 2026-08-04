<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('team_staff')) {
            return;
        }

        Schema::table('team_staff', function (Blueprint $table): void {
            if (! Schema::hasColumn('team_staff', 'color')) {
                $table->string('color', 20)->nullable()->after('position');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('team_staff')) {
            return;
        }

        Schema::table('team_staff', function (Blueprint $table): void {
            if (Schema::hasColumn('team_staff', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};
