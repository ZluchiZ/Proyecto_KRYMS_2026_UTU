<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileEditingTest extends TestCase
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

        Schema::connection('sqlite')->create('cliente', function (Blueprint $table): void {
            $table->string('CI')->primary();
            $table->string('Email_Usuario')->unique();
            $table->string('Nombre')->nullable();
            $table->string('Apellido')->nullable();
            $table->string('Teléfono')->nullable();
            $table->string('Direccion_Entrega')->nullable();
        });

        Schema::connection('sqlite')->create('comercio', function (Blueprint $table): void {
            $table->string('RUT')->primary();
            $table->string('Email_Usuario')->unique();
            $table->string('Nombre_Comercio')->nullable();
            $table->string('Nombre_dueño')->nullable();
            $table->string('Teléfono')->nullable();
            $table->string('Dirección')->nullable();
            $table->string('Horario')->nullable();
        });

        Schema::connection('sqlite')->create('repartidor', function (Blueprint $table): void {
            $table->string('CI')->primary();
            $table->string('Email_Usuario')->unique();
            $table->string('Nombre')->nullable();
            $table->string('Apellido')->nullable();
            $table->string('Teléfono')->nullable();
        });

        Schema::connection('sqlite')->create('usuario', function (Blueprint $table): void {
            $table->string('Email')->primary();
            $table->string('Nombre_de_Usuario');
        });

        Schema::connection('sqlite')->create('reporte', function (Blueprint $table): void {
            $table->id('ID_Reporte');
            $table->string('CI_Cliente');
            $table->string('RUT_Comercio')->nullable();
            $table->string('Categoria', 40)->default('local');
            $table->text('Comentario')->nullable();
            $table->string('Estado_Gestion', 30)->default('Pendiente');
            $table->text('Resolucion')->nullable();
        });

        DB::table('cliente')->insert([
            'CI' => '12345678',
            'Email_Usuario' => 'cliente@example.com',
            'Nombre' => 'Ana',
            'Apellido' => 'Pérez',
        ]);
        DB::table('comercio')->insert([
            'RUT' => '123456789012',
            'Email_Usuario' => 'local@example.com',
            'Nombre_Comercio' => 'Local original',
        ]);
        DB::table('repartidor')->insert([
            'CI' => '87654321',
            'Email_Usuario' => 'repartidor@example.com',
            'Nombre' => 'Luis',
        ]);
    }

    public function test_client_can_update_profile_without_changing_email_or_identifier(): void
    {
        $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
            'email' => 'cliente@example.com',
        ])->get(route('profile.edit'))->assertOk();

        $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
            'email' => 'cliente@example.com',
        ])->patch(route('profile.update'), [
            'nombre' => 'Lucía',
            'apellido' => 'Gómez',
            'telefono' => '099123456',
            'direccion' => 'Calle 1',
            'email' => 'cambiado@example.com',
            'CI' => '11111111',
        ])->assertRedirect(route('cliente.profile'));

        $this->assertDatabaseHas('cliente', [
            'CI' => '12345678',
            'Email_Usuario' => 'cliente@example.com',
            'Nombre' => 'Lucía',
            'Apellido' => 'Gómez',
            'Teléfono' => '099123456',
            'Direccion_Entrega' => 'Calle 1',
        ]);
    }

    public function test_client_can_edit_profile_from_the_view_profile_page(): void
    {
        $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
            'email' => 'cliente@example.com',
        ])->get(route('cliente.profile'))
            ->assertOk()
            ->assertSee('Guardar cambios')
            ->assertSee('name="nombre"', false)
            ->assertSee('name="direccion"', false)
            ->assertDontSee('Editar perfil');
    }

    public function test_client_can_submit_a_categorized_report_from_the_reports_page(): void
    {
        $session = [
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
            'email' => 'cliente@example.com',
        ];

        $this->withSession($session)
            ->get(route('cliente.reportes.index'))
            ->assertOk()
            ->assertSee('Enviar un reporte')
            ->assertSee('El detalle debe tener un máximo de 500 caracteres.')
            ->assertSee('Administración');

        $this->withSession($session)
            ->post(route('cliente.reportes.store'), [
                'categoria' => 'administracion',
                'comentario' => 'Necesito ayuda con mi cuenta.',
            ])
            ->assertRedirect(route('cliente.reportes.index'));

        $this->assertDatabaseHas('reporte', [
            'CI_Cliente' => '12345678',
            'RUT_Comercio' => null,
            'Categoria' => 'administracion',
            'Comentario' => 'Necesito ayuda con mi cuenta.',
            'Estado_Gestion' => 'Pendiente',
        ]);

        $this->withSession($session)
            ->get(route('cliente.reportes.index'))
            ->assertOk()
            ->assertSee('Mis reportes')
            ->assertSee('Necesito ayuda con mi cuenta.');
    }

    public function test_report_requires_a_valid_category_and_at_most_500_characters(): void
    {
        $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
            'email' => 'cliente@example.com',
        ])->post(route('cliente.reportes.store'), [
            'categoria' => 'invalida',
            'comentario' => str_repeat('a', 501),
        ])->assertSessionHasErrors(['categoria', 'comentario']);

        $this->assertSame(0, DB::table('reporte')->count());
    }

    public function test_admin_can_manage_a_persisted_client_report(): void
    {
        DB::table('reporte')->insert([
            'CI_Cliente' => '12345678',
            'RUT_Comercio' => null,
            'Categoria' => 'aplicacion',
            'Comentario' => 'La aplicación no carga el historial.',
            'Estado_Gestion' => 'Pendiente',
        ]);
        $idReporte = DB::table('reporte')->value('ID_Reporte');

        $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
        ])->get(route('dashboard.admin', ['seccion' => 'reportes']))
            ->assertForbidden();

        $sesionAdmin = ['tipo_usuario' => 'administrador', 'usuario_id' => 'admin@example.com'];
        $this->withSession($sesionAdmin)
            ->get(route('dashboard.admin', ['seccion' => 'reportes']))
            ->assertOk()
            ->assertSee('Aplicación')
            ->assertSee('La aplicación no carga el historial.');

        $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
        ])->patch(route('admin.reportes.update', $idReporte), [
            'estado' => 'resuelto',
            'resolucion' => 'Problema corregido.',
        ])->assertForbidden();

        $this->withSession($sesionAdmin)
            ->patch(route('admin.reportes.update', $idReporte), [
                'estado' => 'resuelto',
                'resolucion' => 'Problema corregido.',
            ])
            ->assertRedirect(route('dashboard.admin', ['seccion' => 'reportes']));

        $this->assertDatabaseHas('reporte', [
            'ID_Reporte' => $idReporte,
            'Estado_Gestion' => 'Resuelto',
            'Resolucion' => 'Problema corregido.',
        ]);

        $this->withSession([
            'tipo_usuario' => 'cliente',
            'usuario_id' => '12345678',
        ])->get(route('cliente.reportes.index'))
            ->assertOk()
            ->assertSee('Resuelto')
            ->assertSee('Problema corregido.');
    }

    public function test_local_can_update_business_profile(): void
    {
        $this->withSession([
            'tipo_usuario' => 'comercio',
            'usuario_id' => '123456789012',
            'email' => 'local@example.com',
        ])->get(route('profile.edit'))->assertOk();

        $this->withSession([
            'tipo_usuario' => 'comercio',
            'usuario_id' => '123456789012',
            'email' => 'local@example.com',
        ])->patch(route('profile.update'), [
            'nombre' => 'Nuevo local',
            'nombre_dueno' => 'Dueña',
            'telefono' => '099123456',
            'direccion' => 'Av. Central 123',
            'horario' => '09:00 hrs - 18:00 hrs',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('comercio', [
            'RUT' => '123456789012',
            'Nombre_Comercio' => 'Nuevo local',
            'Nombre_dueño' => 'Dueña',
            'Dirección' => 'Av. Central 123',
            'Horario' => '09:00 hrs - 18:00 hrs',
        ]);
    }

    public function test_courier_can_update_profile(): void
    {
        $this->withSession([
            'tipo_usuario' => 'repartidor',
            'usuario_id' => '87654321',
            'email' => 'repartidor@example.com',
        ])->get(route('profile.edit'))->assertOk();

        $this->withSession([
            'tipo_usuario' => 'repartidor',
            'usuario_id' => '87654321',
            'email' => 'repartidor@example.com',
        ])->patch(route('profile.update'), [
            'nombre' => 'Mario',
            'apellido' => 'Silva',
            'telefono' => '098765432',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('repartidor', [
            'CI' => '87654321',
            'Email_Usuario' => 'repartidor@example.com',
            'Nombre' => 'Mario',
            'Apellido' => 'Silva',
            'Teléfono' => '098765432',
        ]);
    }

    public function test_profile_edit_is_not_available_without_a_supported_account_session(): void
    {
        $this->get(route('profile.edit'))->assertForbidden();
    }
}
