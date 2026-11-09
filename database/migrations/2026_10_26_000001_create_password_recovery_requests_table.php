<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_recovery_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('email', 255);
            $table->string('account_type', 20);
            $table->string('identifier', 30);
            $table->string('status', 20)->default('pending');
            $table->string('token_hash', 64)->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('reviewed_by', 255)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_recovery_requests');
    }
};