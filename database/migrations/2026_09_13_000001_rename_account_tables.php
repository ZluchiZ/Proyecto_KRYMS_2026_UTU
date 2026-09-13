<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'check_email_usuarios_before_insert',
            'check_email_usuarios_before_update',
            'check_email_local_before_insert',
            'check_email_local_before_update',
            'check_email_repartidor_before_insert',
            'check_email_repartidor_before_update',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }

        if (Schema::hasTable('usuarios') && ! Schema::hasTable('cliente')) {
            Schema::rename('usuarios', 'cliente');
        }

        if (Schema::hasTable('local') && ! Schema::hasTable('comercio')) {
            Schema::rename('local', 'comercio');
        }

        if (Schema::hasTable('cliente')) {
            Schema::table('cliente', function (Blueprint $table) {
                if (! Schema::hasColumn('cliente', 'cedula')) {
                    $table->string('cedula')->nullable();
                }
                if (! Schema::hasColumn('cliente', 'apellido')) {
                    $table->string('apellido')->nullable();
                }
                if (! Schema::hasColumn('cliente', 'telefono')) {
                    $table->string('telefono')->nullable();
                }
                if (! Schema::hasColumn('cliente', 'fecha_nacimiento')) {
                    $table->date('fecha_nacimiento')->nullable();
                }
            });
        }

        $this->createEmailTriggers();
    }

    public function down(): void
    {
        foreach ([
            'check_email_usuarios_before_insert',
            'check_email_usuarios_before_update',
            'check_email_local_before_insert',
            'check_email_local_before_update',
            'check_email_repartidor_before_insert',
            'check_email_repartidor_before_update',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }

        if (Schema::hasTable('comercio') && ! Schema::hasTable('local')) {
            Schema::rename('comercio', 'local');
        }

        if (Schema::hasTable('cliente') && ! Schema::hasTable('usuarios')) {
            Schema::rename('cliente', 'usuarios');
        }
    }

    private function createEmailTriggers(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_usuarios_before_insert
            BEFORE INSERT ON cliente
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM comercio WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado en otra cuenta.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_usuarios_before_update
            BEFORE UPDATE ON cliente
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM comercio WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado en otra cuenta.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_local_before_insert
            BEFORE INSERT ON comercio
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM cliente WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado en otra cuenta.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_local_before_update
            BEFORE UPDATE ON comercio
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM cliente WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado en otra cuenta.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_repartidor_before_insert
            BEFORE INSERT ON repartidor
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM cliente WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM comercio WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado en otra cuenta.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_repartidor_before_update
            BEFORE UPDATE ON repartidor
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM cliente WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM comercio WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado en otra cuenta.';
                END IF;
            END
        SQL);
    }
};
