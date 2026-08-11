<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'tipo' => 'nullable|string|in:cooperativa,fundacao',
            'ramo' => 'nullable|string',
            'servicos' => 'nullable|array',
            'servicos.*' => 'string|in:adesao_online,gestao_consultas,gestao_internacoes,gestao_exames,prontuario_eletronico,controle_financeiro,relatorios_gerenciais,gestao_associados,cota_capital,planos_saude,rede_credenciada,reembolsos,tabela_servicos,carteirinha_digital,indicadores_desempenho,campanhas_saude',
            'possui_landing_page' => 'nullable|boolean',
        ];
    }
}
