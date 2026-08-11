<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Hash;
use Tests\TenantTestCase;

class PasswordUpdateTest extends TenantTestCase
{
    public function test_password_can_be_updated(): void
    {
        $user = $this->createTenantUser();

        $response = $this->tenantPut('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], $user);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = $this->createTenantUser();

        $response = $this->tenantPut('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], $user);

        $response->assertSessionHasErrors('current_password');
    }
}
