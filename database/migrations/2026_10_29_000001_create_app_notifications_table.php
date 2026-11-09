<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_type', 30);
            $table->string('recipient_id', 100);
            $table->string('type', 60);
            $table->string('message');
            $table->json('data')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['recipient_type', 'recipient_id', 'read_at']);
            $table->index(['recipient_type', 'recipient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
