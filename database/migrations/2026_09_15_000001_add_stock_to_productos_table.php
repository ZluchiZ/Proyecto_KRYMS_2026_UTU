<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('Producto', 'Stock')) {
            Schema::table('Producto', function (Blueprint $table) {
                $table->unsignedInteger('Stock')->default(0)->after('Disponible');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('Producto', 'Stock')) {
            Schema::table('Producto', function (Blueprint $table) {
                $table->dropColumn('Stock');
            });
        }
    }
};
