<?php

namespace Tests\Feature\Auth;

use Tests\TenantTestCase;

class AuthenticationTest extends TenantTestCase
{
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->tenantGet('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->createTenantUser([
            'has_temporary_password' => false,
        ]);

        $response = $this->tenantPost('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('http://' . $this->tenantDomain . '/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = $this->createTenantUser();

        $this->tenantPost('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = $this->createTenantUser([
            'has_temporary_password' => false,
        ]);

        $response = $this->tenantPost('/logout', [], $user);

        $this->assertGuest();
        $response->assertRedirect('http://' . $this->tenantDomain);
    }
}
