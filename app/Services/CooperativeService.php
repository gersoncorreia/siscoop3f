<?php

namespace App\Services;

use App\Models\Associado;
use App\Models\Dependente;
use App\Models\Convenio;
use App\Models\ContaBancaria;
use App\Models\Coparticipacao;
use App\Models\CotaCapital;
use App\Models\Documento;
use App\Models\PlanoSaude;
use App\Models\Prestador;
use App\Models\Reembolso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CooperativeService
{
    // =========================================================================
    // ASSOCIADOS & DEPENDENTES
    // =========================================================================

    public function criarAssociado(array $dados): Associado
    {
        return DB::transaction(function () use ($dados) {
            return Associado::create($dados);
        });
    }

    public function atualizarAssociado($id, array $dados): bool
    {
        return DB::transaction(function () use ($id, $dados) {
            $associado = Associado::findOrFail($id);
            return $associado->update($dados);
        });
    }

    public function excluirAssociado($id): bool
    {
        return DB::transaction(function () use ($id) {
            $associado = Associado::findOrFail($id);
            return $associado->delete();
        });
    }

    public function criarDependente(array $dados): Dependente
    {
        return DB::transaction(function () use ($dados) {
            return Dependente::create($dados);
        });
    }

    public function atualizarDependente($id, array $dados): bool
    {
        return DB::transaction(function () use ($id, $dados) {
            $dependente = Dependente::findOrFail($id);
            return $dependente->update($dados);
        });
    }

    public function excluirDependente($id): bool
    {
        return DB::transaction(function () use ($id) {
            $dependente = Dependente::findOrFail($id);
            return $dependente->delete();
        });
    }

    // =========================================================================
    // CONVENIOS
    // =========================================================================

    public function criarConvenio(array $dados): Convenio
    {
        return Convenio::create($dados);
    }

    public function atualizarConvenio($id, array $dados): bool
    {
        $convenio = Convenio::findOrFail($id);
        return $convenio->update($dados);
    }

    public function excluirConvenio($id): bool
    {
        $convenio = Convenio::findOrFail($id);
        return $convenio->delete();
    }

    // =========================================================================
    // CONTAS BANCARIAS
    // =========================================================================

    public function criarConta(array $dados): ContaBancaria
    {
        return ContaBancaria::create($dados);
    }

    public function atualizarConta($id, array $dados): bool
    {
        $conta = ContaBancaria::findOrFail($id);
        return $conta->update($dados);
    }

    public function excluirConta($id): bool
    {
        $conta = ContaBancaria::findOrFail($id);
        return $conta->delete();
    }

    // =========================================================================
    // COPARTICIPACOES
    // =========================================================================

    public function criarCoparticipacao($reembolsoId, array $dados): Coparticipacao
    {
        $reembolso = Reembolso::findOrFail($reembolsoId);
        return $reembolso->coparticipacoes()->create($dados);
    }

    public function atualizarCoparticipacao($id, array $dados): bool
    {
        $coparticipacao = Coparticipacao::findOrFail($id);
        return $coparticipacao->update($dados);
    }

    public function excluirCoparticipacao($id): bool
    {
        $coparticipacao = Coparticipacao::findOrFail($id);
        return $coparticipacao->delete();
    }

    // =========================================================================
    // COTA CAPITAL
    // =========================================================================

    public function criarCotaCapital(array $dados): CotaCapital
    {
        return CotaCapital::create($dados);
    }

    public function excluirCotaCapital($id): bool
    {
        $cota = CotaCapital::findOrFail($id);
        return $cota->delete();
    }

    // =========================================================================
    // DOCUMENTOS
    // =========================================================================

    public function criarDocumento($associadoId, array $dados, $arquivo): Documento
    {
        $associado = Associado::findOrFail($associadoId);
        $path = $arquivo->store('documentos', 'local');

        return $associado->documentos()->create([
            'titulo' => $dados['titulo'],
            'tipo' => $dados['tipo'] ?? null,
            'arquivo_path' => $path,
            'status' => $dados['status'] ?? 'pendente',
            'dependente_id' => $dados['dependente_id'] ?? null,
        ]);
    }

    public function excluirDocumento($id): bool
    {
        $documento = Documento::findOrFail($id);
        if ($documento->arquivo_path && Storage::disk('local')->exists($documento->arquivo_path)) {
            Storage::disk('local')->delete($documento->arquivo_path);
        }
        return $documento->delete();
    }

    public function aprovarDocumento($id): bool
    {
        $documento = Documento::findOrFail($id);
        return $documento->update([
            'status' => 'aprovado',
            'motivo_rejeicao' => null,
        ]);
    }

    public function rejeitarDocumento($id, $motivo): bool
    {
        $documento = Documento::findOrFail($id);
        return $documento->update([
            'status' => 'rejeitado',
            'motivo_rejeicao' => $motivo,
        ]);
    }

    // =========================================================================
    // PLANOS DE SAUDE
    // =========================================================================

    public function criarPlano(array $dados): PlanoSaude
    {
        return PlanoSaude::create($dados);
    }

    public function atualizarPlano($id, array $dados): bool
    {
        $plano = PlanoSaude::findOrFail($id);
        return $plano->update($dados);
    }

    public function excluirPlano($id): bool
    {
        $plano = PlanoSaude::findOrFail($id);
        return $plano->delete();
    }

    // =========================================================================
    // PRESTADORES
    // =========================================================================

    public function criarPrestador(array $dados): Prestador
    {
        return Prestador::create($dados);
    }

    public function atualizarPrestador($id, array $dados): bool
    {
        $prestador = Prestador::findOrFail($id);
        return $prestador->update($dados);
    }

    public function excluirPrestador($id): bool
    {
        $prestador = Prestador::findOrFail($id);
        return $prestador->delete();
    }

    // =========================================================================
    // REEMBOLSOS
    // =========================================================================

    public function criarReembolso(array $dados): Reembolso
    {
        return Reembolso::create($dados);
    }

    public function atualizarReembolso($id, array $dados): bool
    {
        $reembolso = Reembolso::findOrFail($id);
        return $reembolso->update($dados);
    }

    public function excluirReembolso($id): bool
    {
        $reembolso = Reembolso::findOrFail($id);
        return $reembolso->delete();
    }

    public function aprovarReembolsoAdmin($id): bool
    {
        $reembolso = Reembolso::findOrFail($id);
        return $reembolso->update([
            'status' => 'aprovado',
            'motivo_recusa' => null,
        ]);
    }

    public function rejeitarReembolsoAdmin($id, $motivo): bool
    {
        $reembolso = Reembolso::findOrFail($id);
        return $reembolso->update([
            'status' => 'rejeitado',
            'motivo_recusa' => $motivo,
        ]);
    }

    // =========================================================================
    // TENANT USERS
    // =========================================================================

    public function atualizarPerfilUsuario($user, array $dados): bool
    {
        $user->fill($dados);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        return $user->save();
    }

    public function criarUsuario(array $dados): User
    {
        return DB::transaction(function () use ($dados) {
            $user = User::create($dados);
            if (isset($dados['role'])) {
                $user->assignRole($dados['role']);
            }
            return $user;
        });
    }

    public function atualizarUsuario($id, array $dados): bool
    {
        return DB::transaction(function () use ($id, $dados) {
            $user = User::findOrFail($id);
            $user->update($dados);
            if (isset($dados['role'])) {
                $user->syncRoles([$dados['role']]);
            }
            return true;
        });
    }

    public function excluirUsuario($id): bool
    {
        return DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            return $user->delete();
        });
    }
}
