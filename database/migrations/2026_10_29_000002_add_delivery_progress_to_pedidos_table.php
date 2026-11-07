<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedido') && ! Schema::hasColumn('pedido', 'Estado_Entrega')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->string('Estado_Entrega', 30)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pedido') && Schema::hasColumn('pedido', 'Estado_Entrega')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->dropColumn('Estado_Entrega');
            });
        }
    }
};
