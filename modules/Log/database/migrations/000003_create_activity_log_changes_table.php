<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('activity_log_changes'))
        Schema::create('activity_log_changes', static function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('activity_log_id')->index();

            $table->string('field', 191)->index();
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('activity_log_id')
                ->references('id')
                ->on('activity_logs')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_changes');
    }
};
