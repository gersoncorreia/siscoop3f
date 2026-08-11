<?php

namespace App\Exports;

use App\Models\Agendamento;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RelatorioAgendamentosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected ?string $dataInicio;
    protected ?string $dataFim;
    protected ?string $status;

    public function __construct(?string $dataInicio = null, ?string $dataFim = null, ?string $status = null)
    {
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->status = $status;
    }

    public function query()
    {
        $query = Agendamento::query()->with(['user', 'prestador']);

        if ($this->dataInicio) {
            $query->whereDate('data_hora', '>=', $this->dataInicio);
        }

        if ($this->dataFim) {
            $query->whereDate('data_hora', '<=', $this->dataFim);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('data_hora', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Paciente / Usuário',
            'E-mail',
            'Tipo',
            'Especialidade',
            'Prestador',
            'Data / Hora',
            'Valor (R$)',
            'Status',
            'Criado em',
        ];
    }

    public function map($agendamento): array
    {
        return [
            $agendamento->id,
            $agendamento->user->name ?? 'N/A',
            $agendamento->user->email ?? 'N/A',
            ucfirst($agendamento->tipo),
            $agendamento->especialidade,
            $agendamento->prestador->nome ?? 'Atendimento Direto',
            $agendamento->data_hora ? date('d/m/Y H:i', strtotime((string)$agendamento->data_hora)) : 'N/A',
            number_format((float)($agendamento->valor_cobrado ?? 0), 2, ',', '.'),
            ucfirst($agendamento->status),
            $agendamento->created_at ? $agendamento->created_at->format('d/m/Y H:i') : 'N/A',
        ];
    }
}
