<?php

namespace Tests\Feature\Auth;

use Tests\TenantTestCase;

class RegistrationTest extends TenantTestCase
{
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->tenantGet('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->tenantPost('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('http://' . $this->tenantDomain . '/dashboard');
    }
}
