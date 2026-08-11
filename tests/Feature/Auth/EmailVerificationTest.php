<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TenantTestCase;

class EmailVerificationTest extends TenantTestCase
{
    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = $this->createTenantUser([
            'email_verified_at' => null,
            'has_temporary_password' => false,
        ]);

        $response = $this->tenantGet('/verify-email', $user);

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = $this->createTenantUser([
            'email_verified_at' => null,
            'has_temporary_password' => false,
        ]);

        \Illuminate\Support\Facades\URL::forceRootUrl('http://' . $this->tenantDomain);

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        \Illuminate\Support\Facades\URL::forceRootUrl(null);

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect('http://' . $this->tenantDomain . '/dashboard?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = $this->createTenantUser([
            'email_verified_at' => null,
            'has_temporary_password' => false,
        ]);

        \Illuminate\Support\Facades\URL::forceRootUrl('http://' . $this->tenantDomain);

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        \Illuminate\Support\Facades\URL::forceRootUrl(null);

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
