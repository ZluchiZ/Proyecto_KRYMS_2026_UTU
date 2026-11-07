<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DomainForeignKeysMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('sqlite');

        Schema::create('cliente', function (Blueprint $table): void {
            $table->string('CI')->primary();
        });
        Schema::create('producto', function (Blueprint $table): void {
            $table->id('ID_Producto');
        });
        Schema::create('carrito', function (Blueprint $table): void {
            $table->id('ID_Carrito');
            $table->string('CI_Cliente', 20);
            $table->unsignedBigInteger('ID_Producto');
        });

        DB::table('cliente')->insert(['CI' => '12345678']);
        DB::table('producto')->insert(['ID_Producto' => 10]);
        DB::table('carrito')->insert([
            'CI_Cliente' => '12345678',
            'ID_Producto' => 10,
        ]);
    }

    public function test_domain_migration_adds_carrito_foreign_keys_with_existing_rows(): void
    {
        $migration = require base_path('database/migrations/2026_10_29_000005_add_domain_foreign_keys.php');
        $migration->up();

        $this->assertTrue(Schema::hasForeignKey('carrito', 'fk_carrito_cliente'));
        $this->assertTrue(Schema::hasForeignKey('carrito', 'fk_carrito_producto'));
        $this->assertCount(2, Schema::getForeignKeys('carrito'));
    }
}
