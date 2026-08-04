<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('admin_sessions'))
        Schema::create('admin_sessions', static function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->index();
            $table->string('guard', 50)->default('web')->index();

            $table->string('session_id', 200)->nullable()->index();

            $table->string('ip', 45)->nullable()->index();
            $table->text('user_agent')->nullable();

            $table->string('device_type', 30)->nullable()->index();
            $table->string('device_brand', 100)->nullable()->index();
            $table->string('device_model', 120)->nullable();

            $table->string('os', 120)->nullable()->index();
            $table->string('os_version', 120)->nullable();

            $table->string('browser', 120)->nullable()->index();
            $table->string('browser_version', 120)->nullable();

            $table->timestamp('login_at')->nullable()->index();
            $table->timestamp('logout_at')->nullable()->index();
            $table->timestamp('last_activity_at')->nullable()->index();

            $table->boolean('is_successful')->default(true)->index();

            $table->timestamps();
        });

        // users cədvəli varsa FK əlavə et (order problemi olsa belə migration qırılmasın)
        if (Schema::hasTable('users')) {
            Schema::table('admin_sessions', static function (Blueprint $table): void {
                $table->foreign('user_id', 'admin_sessions_user_fk')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('admin_sessions')) {
            return;
        }

        // FK adı fərqli ola bilər, ona görə safe şəkildə drop
        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `admin_sessions` DROP FOREIGN KEY `admin_sessions_user_fk`');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `admin_sessions` DROP FOREIGN KEY `admin_sessions_user_id_foreign`');
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::dropIfExists('admin_sessions');
    }
};
