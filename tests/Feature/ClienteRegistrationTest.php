<?php

namespace Tests\Feature;

use App\Mail\RegistrationVerificationCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesRegistrationTables;
use Tests\TestCase;

class ClienteRegistrationTest extends TestCase
{
    use CreatesRegistrationTables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRegistrationTables();
        Mail::fake();
    }

    public function test_registration_creates_a_cliente_record(): void
    {
        Http::fake();

        $response = $this->post(route('cliente.store'), [
            'cedula' => '12345678',
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'Numero' => '099123456',
            'password' => 'password123',
            'password2' => 'password123',
            'nacimiento' => '1990-01-02',
            'opciones' => 'opcion1',
        ]);

        $response->assertRedirect(route('registration.verify.form', ['email' => 'ana@example.com']));

        $this->assertDatabaseHas('registration_verifications', ['email' => 'ana@example.com']);
        $this->assertDatabaseMissing('cliente', ['Email_Usuario' => 'ana@example.com']);
        $this->get(route('registration.verify.form', ['email' => 'ana@example.com']))
            ->assertOk()
            ->assertSee('Código de verificación')
            ->assertSee('name="code"', false);
        $pendingPayload = DB::table('registration_verifications')->where('email', 'ana@example.com')->value('payload');
        $this->assertStringNotContainsString('password123', $pendingPayload);

        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $this->assertNotSame($code, DB::table('registration_verifications')->where('email', 'ana@example.com')->value('code_hash'));

        $this->post(route('registration.verify'), [
            'email' => 'ana@example.com',
            'code' => $code,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('cliente', [
            'CI' => '12345678',
            'Email_Usuario' => 'ana@example.com',
        ]);
        $this->assertDatabaseMissing('registration_verifications', [
            'email' => 'ana@example.com',
        ]);
        $this->assertDatabaseHas('usuario', [
            'Email' => 'ana@example.com',
            'Tipo_Usuario' => 'cliente',
        ]);
        $this->assertTrue(Hash::check(
            'password123',
            DB::table('cliente')->where('Email_Usuario', 'ana@example.com')->value('Contraseña')
        ));
        $this->assertTrue(Hash::check(
            'password123',
            DB::table('usuario')->where('Email', 'ana@example.com')->value('Contraseña')
        ));
    }

    public function test_wrong_code_does_not_create_an_account_and_counts_an_attempt(): void
    {
        $this->post(route('cliente.store'), [
            'cedula' => '12345678',
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'Numero' => '099123456',
            'password' => 'password123',
            'password2' => 'password123',
            'nacimiento' => '1990-01-02',
        ]);

        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $wrongCode = $code === '000000' ? '000001' : '000000';
        $this->from(route('registration.verify.form', ['email' => 'ana@example.com']))
            ->post(route('registration.verify'), [
                'email' => 'ana@example.com',
                'code' => $wrongCode,
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('cliente', ['Email_Usuario' => 'ana@example.com']);
        $this->assertDatabaseHas('registration_verifications', [
            'email' => 'ana@example.com',
            'attempts' => 1,
        ]);
    }

    public function test_an_active_pending_registration_cannot_be_replaced_by_a_new_submission(): void
    {
        $registration = [
            'cedula' => '12345678',
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'Numero' => '099123456',
            'password' => 'password123',
            'password2' => 'password123',
            'nacimiento' => '1990-01-02',
        ];

        $this->post(route('cliente.store'), $registration)
            ->assertRedirect(route('registration.verify.form', ['email' => 'ana@example.com']));

        $registration['nombre'] = 'Otra persona';
        $this->post(route('cliente.store'), $registration)
            ->assertRedirect(route('registration.verify.form', ['email' => 'ana@example.com']));

        $pending = DB::table('registration_verifications')->where('email', 'ana@example.com')->first();
        $payload = json_decode($pending->payload, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Ana', $payload['name']);
        Mail::assertSent(RegistrationVerificationCode::class, 1);
    }

    public function test_expired_code_can_be_resent_and_the_new_code_completes_registration(): void
    {
        $this->post(route('cliente.store'), [
            'cedula' => '12345678',
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'email' => 'ana@example.com',
            'Numero' => '099123456',
            'password' => 'password123',
            'password2' => 'password123',
            'nacimiento' => '1990-01-02',
        ]);

        DB::table('registration_verifications')
            ->where('email', 'ana@example.com')
            ->update([
                'created_at' => now()->subSeconds(61),
                'expires_at' => now()->subMinute(),
            ]);

        $this->from(route('registration.verify.form', ['email' => 'ana@example.com']))
            ->post(route('registration.verify.resend'), ['email' => 'ana@example.com'])
            ->assertRedirect(route('registration.verify.form', ['email' => 'ana@example.com']))
            ->assertSessionHas('success');

        $codes = [];
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$codes): bool {
            $codes[] = $mail->code;

            return true;
        }, 2);

        $this->post(route('registration.verify'), [
            'email' => 'ana@example.com',
            'code' => end($codes),
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('cliente', ['Email_Usuario' => 'ana@example.com']);
    }
}
