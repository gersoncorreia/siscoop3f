<?php

namespace App\Services;

use App\Models\Associado;
use App\Models\Prestador;
use App\Models\PrestadorServico;
use App\Models\SolicitacaoServico;
use Illuminate\Database\Eloquent\Collection;

class PrestadorService
{
    public function getPrestadorByUserId(int $userId): ?Prestador
    {
        return Prestador::where('user_id', $userId)->first();
    }

    public function getDashboardSolicitacoes(int $prestadorId, int $limit = 10): Collection
    {
        return SolicitacaoServico::with(['associado', 'servico'])
            ->where('prestador_id', $prestadorId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function buscarCooperadosAtivos(string $term): array
    {
        $termDigits = preg_replace('/[^0-9]/', '', $term);

        return Associado::where('status', 'ativo')->get()
            ->filter(function($a) use ($term, $termDigits) {
                if (stripos($a->nome, $term) !== false) {
                    return true;
                }
                
                if (!empty($termDigits)) {
                    if ($a->id == (int)$termDigits) {
                        return true;
                    }
                    
                    $cpfLimpo = preg_replace('/[^0-9]/', '', $a->cpf);
                    if (strpos($cpfLimpo, $termDigits) !== false) {
                        return true;
                    }
                }
                
                return false;
            })
            ->take(10)
            ->values()
            ->map(fn($a) => [
                'id' => $a->id,
                'nome' => $a->nome,
                'cpf' => $a->cpf,
                'matricula' => str_pad((string)$a->id, 4, '0', STR_PAD_LEFT),
            ])
            ->toArray();
    }

    public function findAssociadoByIdentificacao(string $identificacao): ?Associado
    {
        $identificacaoDigits = preg_replace('/[^0-9]/', '', $identificacao);
        
        return Associado::all()->first(function($a) use ($identificacao, $identificacaoDigits) {
            if ($a->cpf === $identificacao) return true;
            
            if (!empty($identificacaoDigits)) {
                if ($a->id == (int)$identificacaoDigits) return true;
                
                $cpfLimpo = preg_replace('/[^0-9]/', '', $a->cpf);
                if ($cpfLimpo === $identificacaoDigits) return true;
            }
            
            return false;
        });
    }

    public function isAssociadoInadimplente(Associado $associado): bool
    {
        return $associado->lancamentosFinanceiros()
            ->where('categoria', 'cota_capital')
            ->where('status', 'pendente')
            ->exists();
    }

    public function criarSolicitacao(Associado $associado, Prestador $prestador, PrestadorServico $servico, ?string $observacoes): SolicitacaoServico
    {
        return SolicitacaoServico::create([
            'associado_id' => $associado->id,
            'prestador_id' => $prestador->id,
            'servico_id' => $servico->id,
            'valor_cobrado' => $servico->valor_acordado,
            'observacoes' => $observacoes,
            'status' => 'pendente_autorizacao',
        ]);
    }

    public function criarServico(Prestador $prestador, array $data): PrestadorServico
    {
        return $prestador->servicos()->create($data);
    }

    public function atualizarServico(Prestador $prestador, int $id, array $data): bool
    {
        $servico = $prestador->servicos()->findOrFail($id);
        return $servico->update($data);
    }

    public function excluirServico(Prestador $prestador, int $id): bool
    {
        $servico = $prestador->servicos()->findOrFail($id);
        return $servico->delete();
    }

    public function atualizarPerfil(Prestador $prestador, $user, array $data): void
    {
        $prestador->update([
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'especialidades' => $data['especialidades'],
            'endereco' => $data['endereco'],
        ]);

        $user->update([
            'name' => $data['user_name'],
            'email' => $data['user_email'],
        ]);
    }

    public function alterarSenha($user, string $novaSenha): bool
    {
        return $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($novaSenha),
        ]);
    }
}
