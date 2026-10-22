<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('Pedido', 'CI_Repartidor')) {
            DB::statement('ALTER TABLE `Pedido` MODIFY `CI_Repartidor` varchar(20) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('Pedido', 'CI_Repartidor')) {
            DB::statement('ALTER TABLE `Pedido` MODIFY `CI_Repartidor` varchar(20) NOT NULL');
        }
    }
};