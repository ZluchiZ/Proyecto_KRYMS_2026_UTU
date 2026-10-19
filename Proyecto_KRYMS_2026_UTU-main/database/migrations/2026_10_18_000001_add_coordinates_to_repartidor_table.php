<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repartidor')) {
            return;
        }

        Schema::table('repartidor', function (Blueprint $table): void {
            if (! Schema::hasColumn('repartidor', 'latitud')) {
                $table->decimal('latitud', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('repartidor', 'longitud')) {
                $table->decimal('longitud', 10, 7)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('repartidor')) {
            return;
        }

        $columns = array_filter(['latitud', 'longitud'], fn (string $column): bool => Schema::hasColumn('repartidor', $column));

        if ($columns !== []) {
            Schema::table('repartidor', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};