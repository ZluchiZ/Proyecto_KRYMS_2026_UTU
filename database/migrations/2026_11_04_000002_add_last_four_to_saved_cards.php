<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tarjeta') && ! Schema::hasColumn('tarjeta', 'Ultimos_4')) {
            Schema::table('tarjeta', function (Blueprint $table): void {
                $table->string('Ultimos_4', 4)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tarjeta') && Schema::hasColumn('tarjeta', 'Ultimos_4')) {
            Schema::table('tarjeta', function (Blueprint $table): void {
                $table->dropColumn('Ultimos_4');
            });
        }
    }
};
