<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        Schema::table('languages', function (Blueprint $table) {
            if (!Schema::hasColumn('languages', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('is_default_site');
            }
        });

        // index əlavə et (əgər yoxdursa)
        $this->addIndexIfMissing('languages', ['is_required'], 'languages_is_required_idx');
    }

    public function down(): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        if (!Schema::hasColumn('languages', 'is_required')) {
            return;
        }

        // index adları fərqli ola bilər: custom, default, və ya başqa
        $this->dropIndexIfExists('languages', 'languages_is_required_idx');
        $this->dropIndexIfExists('languages', 'languages_is_required_index'); // Laravel default (table_column_index)

        // fallback: column-a görə tapıb drop et
        $this->dropIndexByColumnsIfExists('languages', ['is_required']);

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }

    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        try {
            $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            if (!empty($exists)) {
                return;
            }

            $cols = implode('`,`', array_map(fn ($c) => (string) $c, $columns));
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$cols}`)");
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * @param array<int,string> $columns
     */
    private function dropIndexByColumnsIfExists(string $table, array $columns): void
    {
        try {
            $wanted = array_values(array_unique(array_map('strval', $columns)));
            sort($wanted);

            $indexes = DB::select("SHOW INDEX FROM `{$table}`");

            $grouped = [];
            foreach ($indexes as $row) {
                $keyName = (string) ($row->Key_name ?? '');
                $colName = (string) ($row->Column_name ?? '');
                if ($keyName === '' || $keyName === 'PRIMARY') {
                    continue;
                }
                $grouped[$keyName][] = $colName;
            }

            foreach ($grouped as $indexName => $idxCols) {
                $idxCols = array_values(array_unique($idxCols));
                sort($idxCols);

                if ($idxCols === $wanted) {
                    $this->dropIndexIfExists($table, $indexName);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
