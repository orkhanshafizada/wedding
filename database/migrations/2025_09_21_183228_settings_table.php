<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index();   // general, social, smtp, security, seo, oauth, system, file_manager, listing, referral, story
            $table->string('key')->index();     // site_title, google_analytics_id, vs.
            $table->json('value')->nullable();  // scalar | array | lang-map
            $table->timestamps();
            $table->unique(['group','key']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('settings');
    }
};
