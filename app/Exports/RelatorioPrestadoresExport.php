<?php

namespace App\Exports;

use App\Models\Prestador;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RelatorioPrestadoresExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return Prestador::query()->with(['user', 'servicos'])->orderBy('nome');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome / Razão Social',
            'E-mail',
            'Tipo',
            'Especialidades',
            'Endereço',
            'Qtd Serviços Cadastrados',
            'Cadastrado em',
        ];
    }

    public function map($prestador): array
    {
        $especialidadesStr = is_array($prestador->especialidades) 
            ? implode(', ', $prestador->especialidades) 
            : ($prestador->especialidades ?? 'Geral');

        return [
            $prestador->id,
            $prestador->nome,
            $prestador->user->email ?? 'N/A',
            ucfirst($prestador->tipo),
            $especialidadesStr,
            $prestador->endereco ?? 'N/A',
            $prestador->servicos->count(),
            $prestador->created_at ? $prestador->created_at->format('d/m/Y H:i') : 'N/A',
        ];
    }
}
