<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();

            // Display names and locale code
            $table->string('name', 100);
            $table->string('native_name', 100)->nullable();
            $table->string('code', 20); // ISO 639-1 or locale, e.g., "en", "az", "tr-TR"

            // Text direction
            $table->boolean('is_rtl')->default(false);

            // Status: "Active" / "Inactive"
            $table->enum('status', ['Active', 'Inactive'])->default('Active');

            // Default flags (must be mutually exclusive per scope)
            $table->boolean('is_default_admin')->default(false);
            $table->boolean('is_default_site')->default(false);

            // Ordering
            $table->unsignedInteger('sort_order')->default(0);

            // Soft delete & timestamps
            $table->softDeletes();
            $table->timestamps();

            // Ensure unique code for non-deleted rows
            $table->unique(['code', 'deleted_at']);
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
