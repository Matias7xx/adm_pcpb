<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OperacaoController extends Controller
{
  public function index(Request $request)
  {
    $this->authorize('adminViewAny', Operacao::class);

    $query = Operacao::with(['user:id,name,matricula', 'resultado'])
      ->orderBy('data_operacao', 'desc');

    if ($request->filled('busca')) {
      $busca = mb_strtolower($request->busca, 'UTF-8');
      $query->where(function ($q) use ($busca) {
        $q->whereRaw('LOWER(nome_operacao) LIKE ?', ["%{$busca}%"])
          ->orWhereRaw('LOWER(unidade_policial_responsavel) LIKE ?', ["%{$busca}%"])
          ->orWhereRaw('LOWER(cidades_alvo) LIKE ?', ["%{$busca}%"]);
      });
    }

    if ($request->filled('origem')) {
      $query->where('origem_operacao', $request->origem);
    }

    if ($request->filled('data_inicio')) {
      $query->whereDate('data_operacao', $request->data_inicio);
    }

    if ($request->boolean('apoio_diop')) {
      $query->whereNotNull('solicitacao_apoio_diop')->where('solicitacao_apoio_diop', '!=', '');
    }

    if ($request->filled('uf_alvo')) {
      $query->whereRaw('ufs_alvo_outros_estados ?? ?', [$request->uf_alvo]);
    }

    $operacoes = $query->paginate(20)->appends($request->query());

    return Inertia::render('Admin/Operacoes/Index', [
      'operacoes' => $operacoes,
      'filters' => $request->only(['busca', 'origem', 'data_inicio', 'apoio_diop', 'uf_alvo']),
      'ufs' => Operacao::getUnidadesFederativas(),
      'origens' => Operacao::getOrigensOperacao(),
    ]);
  }

  public function show(Operacao $operacao)
  {
    $this->authorize('adminViewAny', Operacao::class);

    $operacao->load(['user:id,name,matricula', 'resultado.user:id,name,matricula']);

    return Inertia::render('Admin/Operacoes/Show', [
      'operacao' => $operacao,
      'estatisticas' => $operacao->getEstatisticas(),
      'tiposArma' => \App\Models\ResultadoOperacao::getTiposArma(),
      'tiposEntorpecente' => \App\Models\ResultadoOperacao::getTiposEntorpecente(),
    ]);
  }
}
