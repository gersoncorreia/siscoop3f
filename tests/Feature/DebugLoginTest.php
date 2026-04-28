<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugLoginTest extends TestCase
{
    public function test_tenant_login_redirect()
    {
        // Ignore CSRF
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('http://stfsaude.localhost/login', [
            'email' => 'admin@stfsaude.com.br',
            'password' => 'senha123',
        ]);

        $response->dumpHeaders();
        $response->dumpSession();
    }
}
