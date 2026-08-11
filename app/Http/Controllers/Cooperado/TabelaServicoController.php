<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Models\TabelaServico;
use Inertia\Inertia;

class TabelaServicoController extends Controller
{
    /**
     * Display the public service table for members (read-only).
     */
    public function index()
    {
        $categoria = request('categoria');

        $query = TabelaServico::ativos()->ordenado();

        if ($categoria) {
            $query->categoria($categoria);
        }

        $servicos = $query->get()->map(fn(TabelaServico $s) => [
            'id' => $s->id,
            'codigo' => $s->codigo,
            'nome' => $s->nome,
            'categoria' => $s->categoria,
            'categoria_label' => $s->categoria_label,
            'descricao' => $s->descricao,
            'valor_base' => (float) $s->valor_base,
        ]);

        return Inertia::render('Cooperado/TabelaServicos', [
            'servicos' => $servicos,
            'categorias' => TabelaServico::categorias(),
            'filtroCategoria' => $categoria,
        ]);
    }
}
