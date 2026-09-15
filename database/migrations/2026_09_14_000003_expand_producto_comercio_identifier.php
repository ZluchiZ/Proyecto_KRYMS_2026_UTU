<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Producto', function (Blueprint $table) {
            $table->string('Correo_Comercio', 255)->nullable()->after('RUT_Comercio');
        });
    }

    public function down(): void
    {
        Schema::table('Producto', function (Blueprint $table) {
            $table->dropColumn('Correo_Comercio');
        });
    }
};
