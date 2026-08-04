<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('display_name', 150)->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('name');
            $table->index(['is_active', 'is_super_admin']);
        });

        Schema::create('admin_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('display_name', 200);
            $table->string('group', 100)->index();
            $table->string('scope', 30)->default('system')->index();
            $table->string('module', 80)->index();
            $table->string('action', 50)->index();

            $table->foreignId('menu_id')
                ->nullable()
                ->constrained('menus')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique('name');
            $table->index(['scope', 'module', 'action']);
            $table->index(['menu_id', 'action']);
        });

        Schema::create('admin_permission_role', function (Blueprint $table): void {
            $table->foreignId('role_id')
                ->constrained('admin_roles')
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained('admin_permissions')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('admin_role_user', function (Blueprint $table): void {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained('admin_roles')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_user');
        Schema::dropIfExists('admin_permission_role');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_roles');
    }
};
