<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleAuthenticationNavigationTest extends TestCase
{
    public function test_google_sign_in_uses_native_navigation(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('google.login').'" class="google-btn" data-native-navigation', false);
    }
}
