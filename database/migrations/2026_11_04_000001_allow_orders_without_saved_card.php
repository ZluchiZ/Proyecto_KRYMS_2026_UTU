<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pedido', 'ID_Tarjeta')) {
            $foreignKeys = $this->cardForeignKeys();
            $this->dropForeignKeys($foreignKeys);
            $this->changeCardColumnNullable(true);
            $this->restoreForeignKeys($foreignKeys);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pedido', 'ID_Tarjeta')) {
            return;
        }

        if (DB::table('pedido')->whereNull('ID_Tarjeta')->exists()) {
            throw new \RuntimeException('Cannot make pedido.ID_Tarjeta required while orders without a saved card exist.');
        }

        $foreignKeys = $this->cardForeignKeys();
        $this->dropForeignKeys($foreignKeys);
        $this->changeCardColumnNullable(false);
        $this->restoreForeignKeys($foreignKeys);
    }

    private function cardForeignKeys(): array
    {
        $foreignKeys = array_values(array_filter(
            Schema::getForeignKeys('pedido'),
            fn (array $foreignKey): bool => in_array('ID_Tarjeta', $foreignKey['columns'], true),
        ));

        if ($foreignKeys !== [] || ! Schema::hasColumn('tarjeta', 'ID')) {
            return $foreignKeys;
        }

        return [[
            'name' => 'fk_pedido_tarjeta',
            'columns' => ['ID_Tarjeta'],
            'foreign_table' => 'tarjeta',
            'foreign_columns' => ['ID'],
            'on_update' => 'cascade',
            'on_delete' => 'cascade',
        ]];
    }

    private function changeCardColumnNullable(bool $nullable): void
    {
        if (DB::getDriverName() === 'mysql') {
            $columnType = Schema::hasColumn('tarjeta', 'ID')
                ? Schema::getColumnType('tarjeta', 'ID', true)
                : Schema::getColumnType('pedido', 'ID_Tarjeta', true);
            if (! preg_match('/^(tinyint|smallint|mediumint|int|bigint)(?:\(\d+\))?( unsigned)?$/iD', $columnType)) {
                throw new \RuntimeException('The tarjeta.ID column must use a supported integer type.');
            }

            DB::statement(sprintf(
                'ALTER TABLE `pedido` MODIFY `ID_Tarjeta` %s %s',
                $columnType,
                $nullable ? 'NULL' : 'NOT NULL',
            ));

            return;
        }

        Schema::table('pedido', function (Blueprint $table) use ($nullable): void {
            $table->integer('ID_Tarjeta')->nullable($nullable)->change();
        });
    }

    private function dropForeignKeys(array $foreignKeys): void
    {
        foreach ($foreignKeys as $foreignKey) {
            $exists = collect(Schema::getForeignKeys('pedido'))->contains(
                fn (array $existing): bool => $existing['columns'] === $foreignKey['columns']
                    && $existing['foreign_table'] === $foreignKey['foreign_table']
                    && $existing['foreign_columns'] === $foreignKey['foreign_columns'],
            );
            if (! $exists) {
                continue;
            }

            Schema::table('pedido', function (Blueprint $table) use ($foreignKey): void {
                $constraint = DB::getDriverName() === 'sqlite'
                    ? $foreignKey['columns']
                    : $foreignKey['name'];
                $table->dropForeign($constraint);
            });
        }
    }

    private function restoreForeignKeys(array $foreignKeys): void
    {
        foreach ($foreignKeys as $foreignKey) {
            Schema::table('pedido', function (Blueprint $table) use ($foreignKey): void {
                $definition = $table->foreign($foreignKey['columns'], $foreignKey['name'])
                    ->references($foreignKey['foreign_columns'])
                    ->on($foreignKey['foreign_table']);

                if ($foreignKey['on_update']) {
                    $definition->onUpdate($foreignKey['on_update']);
                }

                if ($foreignKey['on_delete']) {
                    $definition->onDelete($foreignKey['on_delete']);
                }
            });
        }
    }
};
