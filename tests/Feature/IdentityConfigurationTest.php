<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Tests\TestCase;

class IdentityConfigurationTest extends TestCase
{
    public function test_laravel_authentication_uses_the_application_user_model(): void
    {
        $this->assertSame('usuarios', config('auth.guards.web.provider'));
        $this->assertSame(Usuario::class, config('auth.providers.usuarios.model'));
        $this->assertSame('usuarios', config('auth.passwords.users.provider'));
    }

    public function test_client_test_route_is_not_publicly_available(): void
    {
        $this->get('/test-cliente')->assertNotFound();
    }
}
