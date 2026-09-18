<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartAndOrderFlowTest extends TestCase
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

        Schema::create('cliente', function (Blueprint $table) {
            $table->id();
            $table->string('CI')->unique();
            $table->string('Email_Usuario')->nullable();
            $table->string('Teléfono')->nullable();
            $table->string('Direccion_Entrega')->nullable();
            $table->string('direccion')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
        });

        Schema::create('comercio', function (Blueprint $table) {
            $table->string('RUT')->primary();
            $table->string('Email_Usuario');
            $table->string('Nombre_Comercio');
            $table->boolean('Abierto')->default(true);
        });

        Schema::create('producto', function (Blueprint $table) {
            $table->id('ID_Producto');
            $table->string('RUT_Comercio', 20);
            $table->string('Nombre_Producto');
            $table->decimal('Precio', 10, 2)->default(0);
            $table->decimal('Descuento_Porcentaje', 5, 2)->default(0);
            $table->string('Foto_Producto')->nullable();
            $table->boolean('Disponible')->default(true);
        });

        Schema::create('carrito', function (Blueprint $table) {
            $table->id('ID_Carrito');
            $table->unsignedBigInteger('ID_Cliente');
            $table->unsignedBigInteger('ID_Producto');
            $table->integer('Cantidad');
            $table->timestamps();
        });

        Schema::create('pedido', function (Blueprint $table) {
            $table->id('N_Pedido');
            $table->string('CI_Cliente')->nullable();
            $table->string('CI_Repartidor')->nullable();
            $table->decimal('Monto_Total', 10, 2)->nullable();
            $table->string('Metodo_de_pago')->nullable();
            $table->string('Ubicacion')->nullable();
            $table->boolean('Confirmacion_entrega')->nullable();
        });

        Schema::create('subpedido', function (Blueprint $table) {
            $table->id('N_Subpedido');
            $table->unsignedBigInteger('N_Pedido');
            $table->string('RUT_Comercio', 20);
            $table->string('Estado')->default('pendiente');
            $table->decimal('Monto_Total', 10, 2)->nullable();
        });

        Schema::create('detalle_de_pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('N_Subpedido');
            $table->unsignedBigInteger('ID_Producto');
            $table->integer('Cantidad');
            $table->primary(['N_Subpedido', 'ID_Producto']);
        });
    }

    public function test_cart_shows_discounted_total_for_the_cart(): void
    {
        DB::table('cliente')->insert([
            'id' => 9,
            'CI' => '12345678',
            'Email_Usuario' => 'cliente@example.com',
            'Teléfono' => '099123456',
            'Direccion_Entrega' => 'Calle 1',
        ]);

        DB::table('comercio')->insert([
            'RUT' => '111111111111',
            'Email_Usuario' => 'local@example.com',
            'Nombre_Comercio' => 'La Picantería',
            'Abierto' => true,
        ]);

        DB::table('producto')->insert([
            'ID_Producto' => 10,
            'RUT_Comercio' => '111111111111',
            'Nombre_Producto' => 'Milanesa',
            'Precio' => 200,
            'Descuento_Porcentaje' => 10,
            'Foto_Producto' => 'https://example.com/milanesa.jpg',
            'Disponible' => true,
        ]);

        DB::table('carrito')->insert([
            'ID_Cliente' => 9,
            'ID_Producto' => 10,
            'Cantidad' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
        ])->get(route('carrito'));

        $response->assertOk();
        $response->assertSee('Total del carrito');
        $response->assertSee('U$ 360,00');
    }

    public function test_local_can_accept_and_mark_orders_as_ready(): void
    {
        DB::table('comercio')->insert([
            'RUT' => '222222222222',
            'Email_Usuario' => 'local2@example.com',
            'Nombre_Comercio' => 'Otro Local',
            'Abierto' => true,
        ]);

        DB::table('pedido')->insert([
            'N_Pedido' => 5,
            'CI_Cliente' => '12345678',
            'Monto_Total' => 500,
            'Metodo_de_pago' => 'efectivo',
            'Ubicacion' => 'Calle 2',
            'Confirmacion_entrega' => false,
        ]);

        DB::table('subpedido')->insert([
            'N_Subpedido' => 11,
            'N_Pedido' => 5,
            'RUT_Comercio' => '222222222222',
            'Estado' => 'pendiente',
            'Monto_Total' => 500,
        ]);

        $this->withSession([
            'tipo_usuario' => 'comercio',
            'usuario_id' => 'local2@example.com',
            'email' => 'local2@example.com',
        ])->patch(route('pedidos.status', 11), ['estado' => 'aceptado'])
            ->assertRedirect(route('dashboard.local'));

        $this->assertDatabaseHas('subpedido', ['N_Subpedido' => 11, 'Estado' => 'aceptado']);

        $this->withSession([
            'tipo_usuario' => 'comercio',
            'usuario_id' => 'local2@example.com',
            'email' => 'local2@example.com',
        ])->patch(route('pedidos.status', 11), ['estado' => 'listo'])
            ->assertRedirect(route('dashboard.local'));

        $this->assertDatabaseHas('subpedido', ['N_Subpedido' => 11, 'Estado' => 'listo']);
    }
}
