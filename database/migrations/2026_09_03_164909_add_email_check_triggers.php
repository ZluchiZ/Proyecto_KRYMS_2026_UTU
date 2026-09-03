<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_usuarios_before_insert
            BEFORE INSERT ON usuarios
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM local WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado como Local o Repartidor.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_usuarios_before_update
            BEFORE UPDATE ON usuarios
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM local WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado como Local o Repartidor.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_local_before_insert
            BEFORE INSERT ON local
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM usuarios WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado como Cliente o Repartidor.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_local_before_update
            BEFORE UPDATE ON local
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM usuarios WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM repartidor WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado como Cliente o Repartidor.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_repartidor_before_insert
            BEFORE INSERT ON repartidor
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM usuarios WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM local WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado como Cliente o Local.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER check_email_repartidor_before_update
            BEFORE UPDATE ON repartidor
            FOR EACH ROW
            BEGIN
                IF EXISTS (SELECT 1 FROM usuarios WHERE correo = NEW.correo)
                   OR EXISTS (SELECT 1 FROM local WHERE correo = NEW.correo) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El correo ya esta registrado como Cliente o Local.';
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS check_email_usuarios_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS check_email_usuarios_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS check_email_local_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS check_email_local_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS check_email_repartidor_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS check_email_repartidor_before_update');
    }
};
