<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeCliente();
        $this->normalizeComercio();
    }

    public function down(): void
    {
        // The legacy columns are retained so this migration does not destroy data.
    }

    private function normalizeCliente(): void
    {
        if (! Schema::hasTable('cliente')) {
            return;
        }

        $addedId = ! Schema::hasColumn('cliente', 'id');

        Schema::table('cliente', function (Blueprint $table): void {
            if (! Schema::hasColumn('cliente', 'id')) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unique('id');
            }
            if (! Schema::hasColumn('cliente', 'cedula')) {
                $table->string('cedula')->nullable();
            }
            if (! Schema::hasColumn('cliente', 'nombre')) {
                $table->string('nombre')->nullable();
            }
            if (! Schema::hasColumn('cliente', 'apellido')) {
                $table->string('apellido')->nullable();
            }
            if (! Schema::hasColumn('cliente', 'telefono')) {
                $table->string('telefono')->nullable();
            }
            if (! Schema::hasColumn('cliente', 'correo')) {
                $table->string('correo')->nullable();
            }
            if (! Schema::hasColumn('cliente', 'contrasena')) {
                $table->string('contrasena')->nullable();
            }
            if (! Schema::hasColumn('cliente', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('cliente', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (Schema::hasColumn('cliente', 'Email_Usuario')) {
            DB::table('cliente')->whereNull('correo')->update([
                'correo' => DB::raw('`Email_Usuario`'),
            ]);
        }
        if (Schema::hasColumn('cliente', 'CI')) {
            DB::table('cliente')->whereNull('cedula')->update([
                'cedula' => DB::raw('`CI`'),
            ]);
        }
        if (Schema::hasColumn('cliente', 'Nombre')) {
            DB::table('cliente')->whereNull('nombre')->update([
                'nombre' => DB::raw('`Nombre`'),
            ]);
        }
        if (Schema::hasColumn('cliente', 'Apellido')) {
            DB::table('cliente')->whereNull('apellido')->update([
                'apellido' => DB::raw('`Apellido`'),
            ]);
        }
        if (Schema::hasColumn('cliente', 'Teléfono')) {
            DB::table('cliente')->whereNull('telefono')->update([
                'telefono' => DB::raw('`Teléfono`'),
            ]);
        }

        if ($addedId) {
            $this->finalizeLegacyId('cliente', 'CI');
        }
    }

    private function normalizeComercio(): void
    {
        if (! Schema::hasTable('comercio')) {
            return;
        }

        $addedId = ! Schema::hasColumn('comercio', 'id');

        Schema::table('comercio', function (Blueprint $table): void {
            if (! Schema::hasColumn('comercio', 'id')) {
                $table->unsignedBigInteger('id')->nullable();
                $table->unique('id');
            }
            if (! Schema::hasColumn('comercio', 'rut')) {
                $table->string('rut')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'cedula')) {
                $table->string('cedula')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'nombre')) {
                $table->string('nombre')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'direccion')) {
                $table->string('direccion')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'logo')) {
                $table->string('logo')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'numero_cuenta')) {
                $table->string('numero_cuenta')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'correo')) {
                $table->string('correo')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'contrasena')) {
                $table->string('contrasena')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('comercio', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        $copies = [
            'rut' => 'RUT',
            'cedula' => 'CI_Dueño',
            'nombre' => 'Nombre_Comercio',
            'direccion' => 'Dirección',
            'logo' => 'Logo',
            'correo' => 'Email_Usuario',
        ];

        foreach ($copies as $target => $source) {
            if (Schema::hasColumn('comercio', $source)) {
                DB::table('comercio')->whereNull($target)->update([
                    $target => DB::raw("`{$source}`"),
                ]);
            }
        }

        if ($addedId) {
            $this->finalizeLegacyId('comercio', 'RUT');
        }
    }

    private function finalizeLegacyId(string $table, string $legacyKey): void
    {
        $nextId = ((int) DB::table($table)->max('id')) + 1;

        foreach (DB::table($table)->whereNull('id')->get([$legacyKey]) as $row) {
            DB::table($table)
                ->where($legacyKey, $row->{$legacyKey})
                ->whereNull('id')
                ->update(['id' => $nextId++]);
        }

        DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
    }
};