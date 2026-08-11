<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser()
    {
        $user = User::factory()->create(['accepted_lgpd_at' => now()]);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user->assignRole('Super Admin');
        return $user;
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->createAdminUser();

        $response = $this
            ->actingAs($user)
            ->get('/master-admin/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->createAdminUser();

        $response = $this
            ->actingAs($user)
            ->patch('/master-admin/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/master-admin/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->createAdminUser();

        $response = $this
            ->actingAs($user)
            ->patch('/master-admin/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/master-admin/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_cannot_delete_their_account_directly(): void
    {
        $user = $this->createAdminUser();

        $response = $this
            ->actingAs($user)
            ->delete('/master-admin/profile', [
                'password' => 'password',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotNull($user->fresh());
    }
}
