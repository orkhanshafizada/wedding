<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function dropMysqlIndexIfExists(string $table, string $indexName): void
    {
        $dbName = (string) DB::getDatabaseName();

        $exists = (int) DB::table('information_schema.statistics')
            ->where('table_schema', $dbName)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->count();

        if ($exists > 0) {
            DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $indexName));
        }
    }

    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->dropMysqlIndexIfExists('menus', 'menus_link_unique');
            $this->dropMysqlIndexIfExists('menu_translations', 'menu_translations_link_unique');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS menus_link_unique');
            DB::statement('DROP INDEX IF EXISTS menu_translations_link_unique');
            return;
        }

        Schema::table('menus', function (Blueprint $table): void {
            try {
                $table->dropUnique('menus_link_unique');
            } catch (\Throwable) {
            }
        });

        Schema::table('menu_translations', function (Blueprint $table): void {
            try {
                $table->dropUnique('menu_translations_link_unique');
            } catch (\Throwable) {
            }
        });
    }

    public function down(): void
    {
        // Sənin qaydana görə link uniqueness DB-də olmamalıdır.
        // Buna görə down boş saxlanılır.
    }
};
