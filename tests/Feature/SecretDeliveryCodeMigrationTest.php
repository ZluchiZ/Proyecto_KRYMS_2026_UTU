<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecretDeliveryCodeMigrationTest extends TestCase
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
            ],
        ]);
        DB::purge('sqlite');

        Schema::create('pedido', function (Blueprint $table) {
            $table->id('N_Pedido');
        });
    }

    public function test_migration_adds_unique_codes_to_existing_orders(): void
    {
        DB::table('pedido')->insert([
            ['N_Pedido' => 101],
            ['N_Pedido' => 102],
            ['N_Pedido' => 103],
        ]);

        $migration = require database_path('migrations/2026_10_29_000003_add_secret_delivery_code_to_pedidos_table.php');
        $migration->up();

        $codes = DB::table('pedido')->orderBy('N_Pedido')->pluck('Codigo_Secreto');

        $this->assertCount(3, $codes);
        $this->assertCount(3, $codes->unique());
        foreach ($codes as $code) {
            $this->assertSame(12, strlen($code));
        }
    }
}
