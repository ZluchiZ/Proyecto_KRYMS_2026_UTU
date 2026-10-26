<?php

namespace Tests\Feature;

use App\Mail\PasswordRecoveryRequested;
use App\Mail\PasswordResetLink;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\CreatesRegistrationTables;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use CreatesRegistrationTables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRegistrationTables();
        config(['password-recovery.admin_email' => 'admin@example.com']);
        Mail::fake();

        Schema::connection('sqlite')->create('password_recovery_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('account_type');
            $table->string('identifier');
            $table->string('status')->default('pending');
            $table->string('token_hash', 64)->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_recovery_requires_admin_approval_before_password_can_be_changed(): void
    {
        $this->createClientAccount();

        $this->post(route('password-recovery.submit'), [
            'email' => 'ana@example.com',
            'cedula' => '12345678',
        ])->assertRedirect(route('password-recovery.request'))
            ->assertSessionHas('status');

        $recovery = DB::table('password_recovery_requests')->where('email', 'ana@example.com')->first();
        $this->assertSame('pending', $recovery->status);
        $this->assertNull($recovery->token_hash);
        Mail::assertSent(PasswordRecoveryRequested::class);
        Mail::assertNotSent(PasswordResetLink::class);

        $this->post(route('admin.recuperaciones.approve', $recovery->id))->assertForbidden();

        $this->withSession([
            'email' => 'admin@example.com',
            'tipo_usuario' => 'administrador',
            'usuario_id' => 'admin@example.com',
        ])->post(route('admin.recuperaciones.approve', $recovery->id))
            ->assertRedirect(route('dashboard.admin', ['seccion' => 'recuperaciones']));

        $resetUrl = null;
        Mail::assertSent(PasswordResetLink::class, function (PasswordResetLink $mail) use (&$resetUrl): bool {
            $resetUrl = $mail->resetUrl;

            return true;
        });
        $this->get($resetUrl)->assertOk();

        preg_match('#/restablecer-contrasena/(\d+)/([^/]+)$#', parse_url($resetUrl, PHP_URL_PATH), $matches);
        $this->assertCount(3, $matches);

        $this->post(route('password-recovery.reset', [$matches[1], $matches[2]]), [
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check(
            'new-password-456',
            DB::table('cliente')->where('Email_Usuario', 'ana@example.com')->value('Contraseña')
        ));
        $this->assertTrue(Hash::check(
            'new-password-456',
            DB::table('usuario')->where('Email', 'ana@example.com')->value('Contraseña')
        ));
        $this->assertDatabaseHas('password_recovery_requests', [
            'id' => $recovery->id,
            'status' => 'completed',
            'token_hash' => null,
        ]);
        $this->post(route('password-recovery.reset', [$matches[1], $matches[2]]), [
            'password' => 'another-password-789',
            'password_confirmation' => 'another-password-789',
        ])->assertNotFound();
    }

    public function test_incorrect_email_and_document_do_not_create_or_notify_a_request(): void
    {
        $this->createClientAccount();

        $this->post(route('password-recovery.submit'), [
            'email' => 'ana@example.com',
            'cedula' => '87654321',
        ])->assertRedirect(route('password-recovery.request'))
            ->assertSessionHasErrors('cedula');

        $this->assertDatabaseCount('password_recovery_requests', 0);
        Mail::assertNotSent(PasswordRecoveryRequested::class);
    }

    public function test_unknown_email_is_reported_without_creating_or_notifying_a_request(): void
    {
        $this->post(route('password-recovery.submit'), [
            'email' => 'inexistente@example.com',
            'cedula' => '12345678',
        ])->assertRedirect(route('password-recovery.request'))
            ->assertSessionHasErrors([
                'email' => 'No existe una cuenta registrada con ese correo electrónico.',
            ]);

        $this->assertDatabaseCount('password_recovery_requests', 0);
        Mail::assertNotSent(PasswordRecoveryRequested::class);
    }

    private function createClientAccount(): void
    {
        $password = Hash::make('old-password-123');

        DB::table('usuario')->insert([
            'Email' => 'ana@example.com',
            'Nombre_de_Usuario' => 'Ana',
            'Contraseña' => $password,
            'Tipo_Usuario' => 'cliente',
        ]);
        DB::table('cliente')->insert([
            'CI' => '12345678',
            'Email_Usuario' => 'ana@example.com',
            'Contraseña' => $password,
            'Fecha_nacimiento' => '1990-01-02',
            'Nombre' => 'Ana',
            'Apellido' => 'Pérez',
            'Teléfono' => '099123456',
        ]);
    }
}