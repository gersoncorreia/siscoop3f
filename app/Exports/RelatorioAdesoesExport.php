<?php

namespace App\Exports;

use App\Models\Associado;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RelatorioAdesoesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(
        protected ?string $dataInicio = null,
        protected ?string $dataFim = null,
    ) {}

    public function query()
    {
        $query = Associado::query()
            ->withCount('lancamentosFinanceiros as total_taxas_geradas');

        if ($this->dataInicio) {
            $query->where('created_at', '>=', $this->dataInicio . ' 00:00:00');
        }

        if ($this->dataFim) {
            $query->where('created_at', '<=', $this->dataFim . ' 23:59:59');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'CPF',
            'Email',
            'Status',
            'Data Adesão',
            'Forma Pagamento',
            'Autoriza Desconto Folha',
            'Total Taxas Geradas',
            'Criado Em',
        ];
    }

    public function map($associado): array
    {
        $cpf = $associado->cpf ?? '';
        $cpfMasked = strlen($cpf) === 11
            ? substr($cpf, 0, 3) . '.***.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2)
            : $cpf;

        return [
            $associado->id,
            $associado->nome,
            $cpfMasked,
            $associado->email,
            $associado->status,
            $associado->data_adesao ? $associado->data_adesao->format('d/m/Y') : '',
            $associado->forma_pagamento_adesao,
            $associado->autoriza_desconto_folha ? 'Sim' : 'Não',
            $associado->total_taxas_geradas,
            $associado->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
