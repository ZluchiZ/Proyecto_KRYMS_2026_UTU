<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comercio') && ! Schema::hasColumn('comercio', 'Suscripcion_Activa')) {
            Schema::table('comercio', function (Blueprint $table): void {
                $table->boolean('Suscripcion_Activa')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('comercio') && Schema::hasColumn('comercio', 'Suscripcion_Activa')) {
            Schema::table('comercio', function (Blueprint $table): void {
                $table->dropColumn('Suscripcion_Activa');
            });
        }
    }
};
