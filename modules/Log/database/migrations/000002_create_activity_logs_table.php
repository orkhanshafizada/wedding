<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs'))
        Schema::create('activity_logs', static function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_type', 191)->nullable()->index();

            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->string('subject_type', 191)->nullable()->index();

            $table->string('action', 50)->index();
            $table->string('module', 120)->nullable()->index();

            $table->string('route', 191)->nullable()->index();
            $table->text('url')->nullable();
            $table->string('method', 20)->nullable()->index();
            $table->unsignedSmallInteger('status_code')->nullable()->index();

            $table->string('ip', 45)->nullable()->index();
            $table->text('user_agent')->nullable();

            $table->string('request_id', 64)->nullable()->index();

            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
