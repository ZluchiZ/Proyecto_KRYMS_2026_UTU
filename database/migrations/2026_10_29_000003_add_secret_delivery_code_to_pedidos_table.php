<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedido') || Schema::hasColumn('pedido', 'Codigo_Secreto')) {
            return;
        }

        Schema::table('pedido', function (Blueprint $table) {
            $table->string('Codigo_Secreto', 16)->nullable()->unique();
        });

        DB::table('pedido')
            ->whereNull('Codigo_Secreto')
            ->orderBy('N_Pedido')
            ->chunkById(100, function ($pedidos): void {
                foreach ($pedidos as $pedido) {
                    do {
                        $codigo = Str::upper(Str::random(12));
                    } while (DB::table('pedido')->where('Codigo_Secreto', $codigo)->exists());

                    DB::table('pedido')
                        ->where('N_Pedido', $pedido->N_Pedido)
                        ->update(['Codigo_Secreto' => $codigo]);
                }
            }, 'N_Pedido');
    }

    public function down(): void
    {
        if (Schema::hasTable('pedido') && Schema::hasColumn('pedido', 'Codigo_Secreto')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->dropUnique(['Codigo_Secreto']);
                $table->dropColumn('Codigo_Secreto');
            });
        }
    }
};
