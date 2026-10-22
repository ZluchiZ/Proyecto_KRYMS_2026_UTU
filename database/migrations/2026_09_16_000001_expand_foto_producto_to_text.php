<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('Producto') && Schema::hasColumn('Producto', 'Foto_Producto')) {
            DB::statement('ALTER TABLE `Producto` MODIFY `Foto_Producto` TEXT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('Producto') && Schema::hasColumn('Producto', 'Foto_Producto')) {
            DB::statement('ALTER TABLE `Producto` MODIFY `Foto_Producto` VARCHAR(255) NULL');
        }
    }
};
