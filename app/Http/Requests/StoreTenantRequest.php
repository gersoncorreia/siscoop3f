<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|in:cooperativa,fundacao',
            'ramo' => 'nullable|string',
            'admin_nome' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'servicos' => 'nullable|array',
            'servicos.*' => 'string|in:adesao_online,gestao_consultas,gestao_internacoes,gestao_exames,prontuario_eletronico,controle_financeiro,relatorios_gerenciais,gestao_associados,cota_capital,planos_saude,rede_credenciada,reembolsos,tabela_servicos,carteirinha_digital,indicadores_desempenho,campanhas_saude',
            'possui_landing_page' => 'nullable|boolean',
        ];
    }
}
