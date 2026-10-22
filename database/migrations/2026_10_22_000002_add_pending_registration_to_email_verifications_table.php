<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->longText('pending_registration')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->dropColumn('pending_registration');
        });
    }
};
