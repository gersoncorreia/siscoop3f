<?php

namespace App\Http\Controllers;

use App\Models\Associado;
use App\Models\Prestador;
use App\Models\PrestadorServico;
use App\Models\SolicitacaoServico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestadorPortalController extends Controller
{
    /**
     * Obter o Prestador vinculado ao usuário logado
     */
    protected function getPrestador(): Prestador
    {
        $prestador = Prestador::where('user_id', auth()->id())->first();

        if (!$prestador) {
            abort(403, 'Acesso restrito: Nenhum cadastro de Prestador/Parceiro vinculado ao seu usuário.');
        }

        return $prestador;
    }

    /**
     * Dashboard do Prestador / Parceiro
     */
    public function dashboard()
    {
        $prestador = $this->getPrestador();
        
        $solicitacoes = SolicitacaoServico::with(['associado', 'servico'])
            ->where('prestador_id', $prestador->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'paciente' => $s->associado->nome ?? 'N/A',
                'matricula' => str_pad((string)($s->associado->id ?? 0), 4, '0', STR_PAD_LEFT),
                'servico' => $s->servico->nome ?? 'Outros',
                'status' => $s->status,
                'data' => $s->created_at->format('d/m/Y H:i'),
            ]);

        $servicos = $prestador->servicos()->where('ativo', true)->get();

        return Inertia::render('Prestador/Dashboard', [
            'prestador' => $prestador,
            'solicitacoes' => $solicitacoes,
            'servicos' => $servicos,
        ]);
    }

    public function buscarCooperado(Request $request)
    {
        $term = $request->query('q');

        if (empty($term) || strlen($term) < 3) {
            return response()->json([]);
        }

        $termDigits = preg_replace('/[^0-9]/', '', $term);

        // Como o CPF é criptografado no banco, precisamos filtrar pela Collection
        $associados = Associado::where('status', 'ativo')->get()
            ->filter(function($a) use ($term, $termDigits) {
                // Busca por nome exato ou contendo o termo
                if (stripos($a->nome, $term) !== false) {
                    return true;
                }
                
                // Se tiver números, tenta buscar por Matrícula ou CPF
                if (!empty($termDigits)) {
                    if ($a->id == (int)$termDigits) {
                        return true;
                    }
                    
                    // Compara o CPF limpo com o termo digitado
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
            ]);

        return response()->json($associados);
    }

    /**
     * Fluxo Invertido: Prestador inicia o pedido de autorização
     */
    public function solicitarAutorizacao(Request $request)
    {
        $prestador = $this->getPrestador();

        $validated = $request->validate([
            'identificacao' => 'required|string', // Pode ser CPF ou Matrícula
            'servico_id' => 'required|exists:prestador_servicos,id',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        // 1. Buscar o Cooperado (Por CPF ou ID/Matrícula)
        $identificacaoOriginal = $validated['identificacao'];
        $identificacaoDigits = preg_replace('/[^0-9]/', '', $identificacaoOriginal);
        
        // Como o CPF é criptografado, buscamos na collection
        $associado = Associado::all()->first(function($a) use ($identificacaoOriginal, $identificacaoDigits) {
            if ($a->cpf === $identificacaoOriginal) return true;
            
            if (!empty($identificacaoDigits)) {
                if ($a->id == (int)$identificacaoDigits) return true;
                
                $cpfLimpo = preg_replace('/[^0-9]/', '', $a->cpf);
                if ($cpfLimpo === $identificacaoDigits) return true;
            }
            
            return false;
        });

        if (!$associado) {
            return back()->with('error', 'cooperado_nao_encontrado');
        }

        if ($associado->status !== 'ativo') {
            return back()->with('error', 'cooperado_inativo');
        }

        // 2. Validação Financeira (A trava de inadimplência)
        $cotaPendente = $associado->lancamentosFinanceiros()
            ->where('categoria', 'cota_capital')
            ->where('status', 'pendente')
            ->exists();

        if ($cotaPendente) {
            // Retorna o erro flash para a tela do parceiro
            return back()->with('error', 'inadimplente');
        }

        // 3. Tudo OK, gera a solicitação
        $servico = PrestadorServico::findOrFail($validated['servico_id']);

        SolicitacaoServico::create([
            'associado_id' => $associado->id,
            'prestador_id' => $prestador->id,
            'servico_id' => $servico->id,
            'valor_cobrado' => $servico->valor_acordado, // Puxa o valor do catálogo
            'observacoes' => $validated['observacoes'] ?? null,
            'status' => 'pendente_autorizacao', // Aguardando aprovação gerencial (Hélio)
        ]);

        return back()->with('success', 'Pedido de autorização enviado com sucesso. Aguarde a liberação da cooperativa.');
    }

    /**
     * Tela de Catálogo de Serviços/Produtos
     */
    public function servicos()
    {
        $prestador = $this->getPrestador();
        $servicos = $prestador->servicos;

        return Inertia::render('Prestador/Servicos', [
            'prestador' => $prestador,
            'servicos' => $servicos,
        ]);
    }

    public function storeServico(Request $request)
    {
        $prestador = $this->getPrestador();

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_acordado' => 'nullable|numeric|min:0',
            'ativo' => 'boolean',
        ]);

        $prestador->servicos()->create($validated);

        return back()->with('success', 'Item adicionado ao catálogo com sucesso!');
    }

    /**
     * Atualizar um serviço/produto do catálogo
     */
    public function updateServico(Request $request, $id)
    {
        $prestador = $this->getPrestador();
        $servico = $prestador->servicos()->findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_acordado' => 'nullable|numeric|min:0',
            'ativo' => 'boolean',
        ]);

        $servico->update($validated);

        return back()->with('success', 'Item atualizado com sucesso!');
    }

    /**
     * Excluir um serviço/produto do catálogo
     */
    public function deleteServico($id)
    {
        $prestador = $this->getPrestador();
        $servico = $prestador->servicos()->findOrFail($id);

        $servico->delete();

        return back()->with('success', 'Item removido do catálogo.');
    }

    /**
     * Tela de Faturamento (Regra do dia 05)
     */
    public function faturas()
    {
        $prestador = $this->getPrestador();
        
        $faturas = $prestador->faturas()->orderBy('created_at', 'desc')->get();

        return Inertia::render('Prestador/Faturas', [
            'prestador' => $prestador,
            'faturas' => $faturas,
        ]);
    }

    /**
     * Perfil do Prestador
     */
    public function perfil()
    {
        $prestador = $this->getPrestador();
        $user = auth()->user();

        return Inertia::render('Prestador/Perfil', [
            'prestador' => [
                'id' => $prestador->id,
                'nome' => $prestador->nome,
                'tipo' => $prestador->tipo,
                'especialidades' => $prestador->especialidades ?? [],
                'endereco' => $prestador->endereco,
            ],
            'usuario' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Atualizar dados do perfil (prestador + user)
     */
    public function updatePerfil(Request $request)
    {
        $prestador = $this->getPrestador();
        $user = auth()->user();

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string',
            'especialidades' => 'nullable|array',
            'endereco' => 'nullable|string|max:1000',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
        ]);

        $prestador->update([
            'nome' => $validated['nome'],
            'tipo' => $validated['tipo'],
            'especialidades' => $validated['especialidades'],
            'endereco' => $validated['endereco'],
        ]);

        $user->update([
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
        ]);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Alterar senha de acesso
     */
    public function updateSenha(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }
}
