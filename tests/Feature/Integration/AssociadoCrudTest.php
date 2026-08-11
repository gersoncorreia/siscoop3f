<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use App\Models\Associado;
use Tests\TenantTestCase;

class AssociadoCrudTest extends TenantTestCase
{


    /** @test */
    public function test_can_create_associado_with_valid_data()
    {
        $user = $this->createTenantUser();
        $user->assignRole('Presidente');

        $response = $this->tenantPost('/associados', [
            'nome' => 'Maria da Silva',
            'cpf' => '123.456.789-00',
            'status' => 'ativo',
        ], $user);


        // Deve redirecionar após sucesso (302)
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Deve ter sido persistido no banco do tenant
        $associado = Associado::where('nome', 'Maria da Silva')->first();
        $this->assertNotNull($associado);
        $this->assertEquals('123.456.789-00', $associado->cpf);
        $this->assertEquals('ativo', $associado->status);
    }

    /** @test */
    public function test_cannot_create_associado_without_required_fields()
    {
        $user = $this->createTenantUser();
        $user->assignRole('Presidente');

        $response = $this->tenantPost('/associados', [], $user);


        $response->assertSessionHasErrors(['nome', 'cpf', 'status']);
    }

    /** @test */
    public function test_cannot_create_associado_with_invalid_status()
    {
        $user = $this->createTenantUser();
        $user->assignRole('Presidente');

        $response = $this->tenantPost('/associados', [
            'nome' => 'Carlos Teste',
            'cpf' => '999.888.777-66',
            'status' => 'invalido',
        ], $user);

        $response->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function test_cannot_create_associado_with_duplicate_cpf()
    {
        $user = $this->createTenantUser();
        $user->assignRole('Presidente');

        Associado::create([
            'nome' => 'Primeiro',
            'cpf' => '111.222.333-44',
            'status' => 'ativo',
        ]);

        $response = $this->tenantPost('/associados', [
            'nome' => 'Segundo',
            'cpf' => '111.222.333-44',
            'status' => 'ativo',
        ], $user);

        $response->assertSessionHasErrors(['cpf']);
    }

    /** @test */
    public function test_can_update_associado()
    {
        $user = $this->createTenantUser();
        $user->assignRole('Presidente');

        $associado = Associado::create([
            'nome' => 'Original',
            'cpf' => '555.666.777-88',
            'status' => 'ativo',
        ]);

        $response = $this->tenantPut("/associados/{$associado->id}", [
            'nome' => 'Atualizado',
            'cpf' => '555.666.777-88',
            'status' => 'inativo',
        ], $user);

        $response->assertRedirect();

        $this->assertDatabaseHas('associados', [
            'id' => $associado->id,
            'nome' => 'Atualizado',
            'status' => 'inativo',
        ]);
    }

    /** @test */
    public function test_can_delete_associado()
    {
        $user = $this->createTenantUser();
        $user->assignRole('Presidente');

        $associado = Associado::create([
            'nome' => 'Para Excluir',
            'cpf' => '444.333.222-11',
            'status' => 'ativo',
        ]);

        $response = $this->tenantDelete("/associados/{$associado->id}", $user);

        $response->assertRedirect();
        $this->assertDatabaseMissing('associados', ['id' => $associado->id]);
    }
}
