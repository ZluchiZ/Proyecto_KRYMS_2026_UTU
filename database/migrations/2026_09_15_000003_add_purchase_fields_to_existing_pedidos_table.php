<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Pedido')) {
            return;
        }

        Schema::table('Pedido', function (Blueprint $table) {
            if (! Schema::hasColumn('Pedido', 'ID_Cliente')) {
                $table->unsignedBigInteger('ID_Cliente')->nullable()->after('N_Pedido');
            }
            if (! Schema::hasColumn('Pedido', 'ID_Producto')) {
                $table->unsignedBigInteger('ID_Producto')->nullable()->after('ID_Cliente');
            }
            if (! Schema::hasColumn('Pedido', 'Cantidad')) {
                $table->unsignedInteger('Cantidad')->default(1)->after('ID_Producto');
            }
            if (! Schema::hasColumn('Pedido', 'Total')) {
                $table->decimal('Total', 10, 2)->nullable()->after('Cantidad');
            }
            if (! Schema::hasColumn('Pedido', 'Direccion_Envio')) {
                $table->string('Direccion_Envio', 500)->nullable()->after('Total');
            }
            if (! Schema::hasColumn('Pedido', 'Telefono_Contacto')) {
                $table->string('Telefono_Contacto', 30)->nullable()->after('Direccion_Envio');
            }
            if (! Schema::hasColumn('Pedido', 'Referencias')) {
                $table->text('Referencias')->nullable()->after('Telefono_Contacto');
            }
            if (! Schema::hasColumn('Pedido', 'Metodo_Pago')) {
                $table->string('Metodo_Pago', 30)->nullable()->after('Referencias');
            }
            if (! Schema::hasColumn('Pedido', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('Pedido', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (Schema::hasColumn('Pedido', 'CI_Repartidor')) {
            DB::statement('ALTER TABLE `Pedido` MODIFY `CI_Repartidor` varchar(20) NULL');
        }
        if (Schema::hasColumn('Pedido', 'ID_Tarjeta')) {
            DB::statement('ALTER TABLE `Pedido` MODIFY `ID_Tarjeta` int NULL');
        }
        if (Schema::hasColumn('Pedido', 'CI_Cliente')) {
            DB::statement('ALTER TABLE `Pedido` MODIFY `CI_Cliente` varchar(20) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('Pedido')) {
            return;
        }

        Schema::table('Pedido', function (Blueprint $table) {
            foreach ([
                'ID_Cliente',
                'ID_Producto',
                'Cantidad',
                'Total',
                'Direccion_Envio',
                'Telefono_Contacto',
                'Referencias',
                'Metodo_Pago',
                'created_at',
                'updated_at',
            ] as $column) {
                if (Schema::hasColumn('Pedido', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
