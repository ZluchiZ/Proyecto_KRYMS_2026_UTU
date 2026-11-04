<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RELATIONS = [
        ['administrador', 'Email_Usuario', 'usuario', 'Email', 'cascade', 'fk_administrador_usuario'],
        ['cliente', 'Email_Usuario', 'usuario', 'Email', 'cascade', 'fk_cliente_usuario'],
        ['comercio', 'Email_Usuario', 'usuario', 'Email', 'cascade', 'fk_comercio_usuario'],
        ['repartidor', 'Email_Usuario', 'usuario', 'Email', 'cascade', 'fk_repartidor_usuario'],
        ['tarjeta', 'CI_Cliente', 'cliente', 'CI', 'cascade', 'fk_tarjeta_cliente'],
        ['pedido', 'CI_Cliente', 'cliente', 'CI', 'cascade', 'fk_pedido_cliente'],
        ['pedido', 'CI_Repartidor', 'repartidor', 'CI', 'cascade', 'fk_pedido_repartidor'],
        ['pedido', 'ID_Tarjeta', 'tarjeta', 'ID', 'cascade', 'fk_pedido_tarjeta'],
        ['producto', 'RUT_Comercio', 'comercio', 'RUT', 'cascade', 'fk_producto_comercio'],
        ['carrito', 'CI_Cliente', 'cliente', 'CI', 'cascade', 'fk_carrito_cliente'],
        ['carrito', 'ID_Producto', 'producto', 'ID_Producto', 'cascade', 'fk_carrito_producto'],
        ['subpedido', 'N_Pedido', 'pedido', 'N_Pedido', 'cascade', 'fk_subpedido_pedido'],
        ['subpedido', 'RUT_Comercio', 'comercio', 'RUT', 'cascade', 'fk_subpedido_comercio'],
        ['detalle_de_pedido', 'N_Subpedido', 'subpedido', 'N_Subpedido', 'cascade', 'fk_detalle_subpedido'],
        ['detalle_de_pedido', 'ID_Producto', 'producto', 'ID_Producto', 'cascade', 'fk_detalle_producto'],
        ['pedido_repartidor_estado', 'N_Pedido', 'pedido', 'N_Pedido', 'cascade', 'fk_historial_pedido'],
        ['pedido_repartidor_estado', 'CI_Repartidor', 'repartidor', 'CI', 'cascade', 'fk_historial_repartidor'],
        ['reporte', 'CI_Cliente', 'cliente', 'CI', 'cascade', 'fk_reporte_cliente'],
        ['reporte', 'RUT_Comercio', 'comercio', 'RUT', 'cascade', 'fk_reporte_comercio'],
        ['reseña', 'CI_Cliente', 'cliente', 'CI', 'cascade', 'fk_resena_cliente'],
        ['reseña', 'RUT_Comercio', 'comercio', 'RUT', 'cascade', 'fk_resena_comercio'],
        ['reseña', 'CI_Repartidor', 'repartidor', 'CI', 'cascade', 'fk_resena_repartidor'],
        ['reseña', 'N_Pedido', 'pedido', 'N_Pedido', 'cascade', 'fk_resena_pedido'],
        ['vehículo', 'CI_Repartidor', 'repartidor', 'CI', 'cascade', 'fk_vehiculo_repartidor'],
        ['app_notifications', 'order_id', 'pedido', 'N_Pedido', 'set null', 'fk_notificacion_pedido'],
    ];

    public function up(): void
    {
        foreach (self::RELATIONS as [$table, $column, $referencedTable, $referencedColumn, $onDelete, $constraint]) {
            if (! Schema::hasTable($table)
                || ! Schema::hasColumn($table, $column)
                || ! Schema::hasTable($referencedTable)
                || ! Schema::hasColumn($referencedTable, $referencedColumn)
                || $this->hasForeignKey($table, $column, $referencedTable, $referencedColumn)) {
                continue;
            }

            $this->failIfOrphansExist($table, $column, $referencedTable, $referencedColumn);
            $this->matchIntegerColumnType($table, $column, $referencedTable, $referencedColumn);

            Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable, $referencedColumn, $onDelete, $constraint): void {
                $foreignKey = $blueprint->foreign($column, $constraint)
                    ->references($referencedColumn)
                    ->on($referencedTable)
                    ->onUpdate('cascade');

                if ($onDelete === 'set null') {
                    $foreignKey->nullOnDelete();
                } else {
                    $foreignKey->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::RELATIONS as [$table, , , , , $constraint]) {
            if (! Schema::hasTable($table) || ! $this->hasConstraintNamed($table, $constraint)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($constraint): void {
                $blueprint->dropForeign($constraint);
            });
        }
    }

    private function hasForeignKey(string $table, string $column, string $referencedTable, string $referencedColumn): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (array_map('strtolower', $foreignKey['columns']) === [strtolower($column)]
                && strtolower($foreignKey['foreign_table']) === strtolower($referencedTable)
                && array_map('strtolower', $foreignKey['foreign_columns']) === [strtolower($referencedColumn)]) {
                return true;
            }
        }

        return false;
    }

    private function hasConstraintNamed(string $table, string $constraint): bool
    {
        return Schema::hasForeignKey($table, $constraint);
    }

    private function failIfOrphansExist(string $table, string $column, string $referencedTable, string $referencedColumn): void
    {
        $orphans = DB::table("{$table} as child")
            ->leftJoin("{$referencedTable} as parent", "child.{$column}", '=', "parent.{$referencedColumn}")
            ->whereNotNull("child.{$column}")
            ->whereNull("parent.{$referencedColumn}")
            ->count();

        if ($orphans > 0) {
            throw new RuntimeException("Cannot add foreign key for {$table}.{$column}: {$orphans} orphaned row(s) reference {$referencedTable}.{$referencedColumn}.");
        }
    }

    private function matchIntegerColumnType(string $table, string $column, string $referencedTable, string $referencedColumn): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $child = Schema::getColumn($table, $column);
        $parentType = Schema::getColumnType($referencedTable, $referencedColumn, true);

        if ($child === [] || strcasecmp($child['type'], $parentType) === 0) {
            return;
        }

        if (! preg_match('/^(tinyint|smallint|mediumint|int|bigint)(?:\(\d+\))?( unsigned)?$/iD', $parentType)) {
            return;
        }

        $nullable = $child['nullable'] ? 'NULL' : 'NOT NULL';
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$parentType} {$nullable}");
    }
};