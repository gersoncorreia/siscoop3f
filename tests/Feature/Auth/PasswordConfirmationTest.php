<?php

namespace Tests\Feature\Auth;

use Tests\TenantTestCase;

class PasswordConfirmationTest extends TenantTestCase
{
    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = $this->createTenantUser([
            'has_temporary_password' => false,
        ]);

        $response = $this->tenantGet('/confirm-password', $user);

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = $this->createTenantUser([
            'has_temporary_password' => false,
        ]);

        $response = $this->tenantPost('/confirm-password', [
            'password' => 'password',
        ], $user);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = $this->createTenantUser([
            'has_temporary_password' => false,
        ]);

        $response = $this->tenantPost('/confirm-password', [
            'password' => 'wrong-password',
        ], $user);

        $response->assertSessionHasErrors();
    }
}
