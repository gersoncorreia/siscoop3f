<?php

namespace App\Exports;

use App\Models\Dependente;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RelatorioDependentesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(
        protected ?string $dataInicio = null,
        protected ?string $dataFim = null,
    ) {}

    public function query()
    {
        $query = Dependente::query()->with('associado');

        if ($this->dataInicio) {
            $query->where('created_at', '>=', $this->dataInicio . ' 00:00:00');
        }

        if ($this->dataFim) {
            $query->where('created_at', '<=', $this->dataFim . ' 23:59:59');
        }

        return $query->orderBy('nome');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Associado (Titular)',
            'Nome Dependente',
            'CPF',
            'Parentesco',
            'Tipo Dependência',
            'Tem Acesso Portal',
            'Status',
            'Criado Em',
        ];
    }

    public function map($dependente): array
    {
        $cpf = $dependente->cpf ?? '';
        $cpfMasked = strlen($cpf) === 11
            ? substr($cpf, 0, 3) . '.***.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2)
            : $cpf;

        return [
            $dependente->id,
            $dependente->associado?->nome ?? 'N/A',
            $dependente->nome,
            $cpfMasked,
            $dependente->parentesco,
            $dependente->tipo_dependencia,
            $dependente->autoriza_acesso ? 'Sim' : 'Não',
            $dependente->status,
            $dependente->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
