<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comercio') && ! Schema::hasColumn('comercio', 'Abierto')) {
            Schema::table('comercio', function (Blueprint $table): void {
                $table->boolean('Abierto')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('comercio') && Schema::hasColumn('comercio', 'Abierto')) {
            Schema::table('comercio', function (Blueprint $table): void {
                $table->dropColumn('Abierto');
            });
        }
    }
};
