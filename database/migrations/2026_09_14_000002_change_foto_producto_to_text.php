<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Producto', function (Blueprint $table) {
            $table->text('Foto_Producto')->change();
        });
    }

    public function down(): void
    {
        Schema::table('Producto', function (Blueprint $table) {
            $table->string('Foto_Producto')->change();
        });
    }
};
