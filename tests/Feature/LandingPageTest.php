<?php

use App\Models\LandingPageSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TenantTestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = $this->createTenantUser();
    $this->adminUser->assignRole('Administrador Cooperativa');
    
    $this->commonUser = $this->createTenantUser(); // Sem role administrativo
});

it('publico pode acessar a landing page', function () {
    $response = $this->tenantGet('/');
    
    $response->assertStatus(200);
});

it('admin pode ver tela de configuracoes da landing page', function () {
    $response = $this->tenantGet('/configuracoes/landing-page', $this->adminUser);
    
    $response->assertStatus(200);
});

it('admin pode atualizar configuracoes da landing page', function () {
    $payload = [
        'theme_preset' => 'indigo',
        'primary_color' => '#4f46e5',
        'secondary_color' => '#06b6d4',
        'section_hero_enabled' => true,
        'hero_title' => 'Novo Titulo Teste',
        'hero_subtitle' => 'Subtitulo atualizado para testes',
        'section_about_enabled' => true,
        'about_title' => 'Quem Somos Nós',
        'about_text' => 'Texto sobre a cooperativa atualizado.',
        'section_features_enabled' => true,
        'features' => [
            [
                'icon' => 'ShieldCheckIcon',
                'title' => 'Segurança Garantida',
                'description' => 'Testando descrição do diferencial.'
            ]
        ],
        'section_contact_enabled' => true,
        'contact_email' => 'suporte@cooptest.com',
        'contact_phone' => '123456789',
        'contact_address' => 'Endereço de Teste',
        'social_facebook' => 'https://facebook.com/cooptest',
        'social_instagram' => 'https://instagram.com/cooptest',
        'social_linkedin' => 'https://linkedin.com/in/cooptest',
    ];
    
    $response = $this->tenantPost('/configuracoes/landing-page', $payload, $this->adminUser);
    
    $response->assertRedirect();
    
    $settings = LandingPageSetting::first();
    expect($settings)->not->toBeNull();
    expect($settings->hero_title)->toBe('Novo Titulo Teste');
    expect($settings->theme_preset)->toBe('indigo');
    expect($settings->features[0]['title'])->toBe('Segurança Garantida');
});

it('usuario comum nao pode acessar configuracoes da landing page', function () {
    $response = $this->tenantGet('/configuracoes/landing-page', $this->commonUser);
    
    // Deve ser barrado pelo middleware de role (redireciona para home ou retorna 403 dependendo do middleware)
    // Nosso middleware de role geralmente redireciona ou aborta. Vamos garantir que não é 200.
    expect($response->status())->not->toBe(200);
});
