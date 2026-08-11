<?php

namespace App\Exports;

use App\Models\LancamentoFinanceiro;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RelatorioFinanceiroExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(
        protected ?string $dataInicio = null,
        protected ?string $dataFim = null,
        protected ?string $status = null,
    ) {}

    public function query()
    {
        $query = LancamentoFinanceiro::query()->with('associado');

        if ($this->dataInicio) {
            $query->where('created_at', '>=', $this->dataInicio . ' 00:00:00');
        }

        if ($this->dataFim) {
            $query->where('created_at', '<=', $this->dataFim . ' 23:59:59');
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Associado',
            'Tipo',
            'Categoria',
            'Valor (R$)',
            'Data Vencimento',
            'Status',
            'Gateway',
            'Criado Em',
        ];
    }

    public function map($lancamento): array
    {
        return [
            $lancamento->id,
            $lancamento->associado?->nome ?? 'N/A',
            $lancamento->tipo,
            $lancamento->categoria,
            number_format((float) $lancamento->valor, 2, ',', '.'),
            $lancamento->data_vencimento ? date('d/m/Y', strtotime($lancamento->data_vencimento)) : '',
            $lancamento->status,
            $lancamento->gateway,
            $lancamento->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
