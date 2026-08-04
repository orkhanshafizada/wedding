<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('iso2', 2)->index();
            $table->char('iso3', 3)->nullable()->index();
            $table->string('numcode', 6)->nullable();
            $table->string('un_member', 12)->nullable();
            $table->string('calling_code', 16)->nullable();
            $table->string('cctld', 8)->nullable();
            $table->boolean('is_active')->default(true);
            // Dil adları JSON:
            // short_names: {"en": "...", "az": "..."}
            // long_names : {"en": "...", "az": "..."}
            $table->json('short_names');
            $table->json('long_names');

            $table->timestamps();

            $table->unique('iso2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
