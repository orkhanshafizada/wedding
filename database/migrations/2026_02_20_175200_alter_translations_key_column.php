<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Mövcud unique/index adını bilmədiyimiz üçün ən təhlükəsizi:
        // əvvəl indexes-i oxuyub adları tapmaqdır. Amma migration-da dinamik etmək çətindir.
        // Praktikada səndə çox vaxt bu ad olur:
        // translations_key_locale_unique
        // Əgər fərqlidirsə, aşağıda düzəldəcəksən (qeyd etdim).

        Schema::table('translations', function (Blueprint $table) {
            // unique adı standartdırsa:
            $table->dropUnique('translations_key_locale_unique');
        });

        // 2) key sütununu TEXT et
        Schema::table('translations', function (Blueprint $table) {
            $table->text('key')->change();
        });

        // 3) Prefix unique yarat (MySQL üçün)
        // UNIQUE(key(191), locale)
        DB::statement('ALTER TABLE `translations` ADD UNIQUE `translations_key_locale_unique` (`key`(191), `locale`)');
    }

    public function down(): void
    {
        // prefix unique-i sil
        Schema::table('translations', function (Blueprint $table) {
            $table->dropUnique('translations_key_locale_unique');
        });

        // key-i geri varchar et
        Schema::table('translations', function (Blueprint $table) {
            $table->string('key', 191)->change();
        });

        // klassik unique geri
        Schema::table('translations', function (Blueprint $table) {
            $table->unique(['key', 'locale'], 'translations_key_locale_unique');
        });
    }
};
