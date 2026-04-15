<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', static function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('scope')->default('tenant');
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_role_user', static function (Blueprint $table): void {
            $table->uuid('user_id');
            $table->foreignId('role_id');
            $table->timestamps();

            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};