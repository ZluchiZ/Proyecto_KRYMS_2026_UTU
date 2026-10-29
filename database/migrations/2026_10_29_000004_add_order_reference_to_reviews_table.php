<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseña') && ! Schema::hasColumn('reseña', 'N_Pedido')) {
            Schema::table('reseña', function (Blueprint $table) {
                $table->unsignedBigInteger('N_Pedido')->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseña') && Schema::hasColumn('reseña', 'N_Pedido')) {
            Schema::table('reseña', function (Blueprint $table) {
                $table->dropUnique(['N_Pedido']);
                $table->dropColumn('N_Pedido');
            });
        }
    }
};
