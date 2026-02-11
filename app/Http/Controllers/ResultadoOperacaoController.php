<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResultadoOperacaoRequest;
use App\Models\ResultadoOperacao;
use App\Models\Operacao;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ResultadoOperacaoController extends Controller
{
  /**
   * Verifica permissão de forma case-insensitive e sem espaços
   */
  private function temPermissao($resultado): bool
  {
    $lotacao = auth()->user()->lotacao;

    if (!$lotacao) {
      return false;
    }

    // Se for um ResultadoOperacao, pega a unidade dele
    if ($resultado instanceof ResultadoOperacao) {
      return mb_strtolower(
        trim($resultado->unidade_policial_responsavel ?? ''),
      ) === mb_strtolower(trim($lotacao));
    }

    // Se for uma Operacao
    if ($resultado instanceof Operacao) {
      return mb_strtolower(
        trim($resultado->unidade_policial_responsavel ?? ''),
      ) === mb_strtolower(trim($lotacao));
    }

    return false;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user = auth()->user();

    $query = ResultadoOperacao::with(['operacao', 'user'])
      ->where('unidade_policial_responsavel', $user->lotacao)
      ->orderBy('created_at', 'desc');

    if ($request->filled('data_inicio')) {
      $query->where('created_at', '>=', $request->data_inicio);
    }

    if ($request->filled('data_fim')) {
      $query->where('created_at', '<=', $request->data_fim);
    }

    if ($request->filled('busca')) {
      $busca = mb_strtolower($request->busca, 'UTF-8');

      $query->where(function ($q) use ($busca) {
        $q->whereHas('operacao', function ($subQ) use ($busca) {
          $subQ->whereRaw('LOWER(nome_operacao) LIKE ?', ["%{$busca}%"]);
        });
      });
    }

    $resultados = $query->paginate(10);

    return Inertia::render('ResultadosOperacao/Index', [
      'resultados' => $resultados,
      'filtros' => $request->only(['data_inicio', 'data_fim', 'busca']),
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(Request $request)
  {
    // Buscar operação se vier o ID
    $operacao = null;
    if ($request->filled('operacao_id')) {
      $operacao = Operacao::findOrFail($request->operacao_id);

      // Verificar permissão
      if (!$this->temPermissao($operacao)) {
        abort(
          403,
          'Você não tem permissão para registrar resultado desta operação.',
        );
      }

      // Verificar se já existe resultado para esta operação
      if ($operacao->resultado()->exists()) {
        return redirect()
          ->route('resultados-operacao.show', $operacao->resultado->id)
          ->with('info', 'Esta operação já possui resultado cadastrado.');
      }
    }

    // Buscar todas as operações da unidade que ainda não têm resultado
    $operacoesDisponiveis = Operacao::where(
      'unidade_policial_responsavel',
      auth()->user()->lotacao,
    )
      ->whereDoesntHave('resultado')
      ->orderBy('data_operacao', 'desc')
      ->get()
      ->map(function ($op) {
        return [
          'id' => $op->id,
          'label' => $op->nome_operacao . ' - ' . $op->data_operacao_formatada,
        ];
      });

    return Inertia::render('ResultadosOperacao/Create', [
      'operacao' => $operacao,
      'operacoesDisponiveis' => $operacoesDisponiveis,
      'opcoes' => [
        'tipos_arma' => ResultadoOperacao::getTiposArma(),
        'tipos_entorpecente' => ResultadoOperacao::getTiposEntorpecente(),
      ],
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(ResultadoOperacaoRequest $request)
  {
    $user = auth()->user();
    $operacao = Operacao::findOrFail($request->operacao_id);

    // Verificar permissão
    if (!$this->temPermissao($operacao)) {
      abort(
        403,
        'Você não tem permissão para registrar resultado desta operação.',
      );
    }

    // Verificar se já existe resultado
    if ($operacao->resultado()->exists()) {
      return redirect()
        ->route('resultados-operacao.show', $operacao->resultado->id)
        ->with('info', 'Esta operação já possui resultado cadastrado.');
    }

    // Preencher dados automáticos do usuário logado
    $dados = $request->validated();
    $dados['user_id'] = $user->id;
    $dados['policial_responsavel_nome'] = $user->name;
    $dados['policial_responsavel_matricula'] = $user->matricula;
    $dados['unidade_policial_responsavel'] = $user->lotacao;

    // Autoridade e policial já vêm do formulário (editáveis)

    $resultado = ResultadoOperacao::create($dados);

    return redirect()
      ->route('resultados-operacao.show', ['resultado' => $resultado->id])
      ->with('success', 'Resultado da operação cadastrado com sucesso!');
  }

  /**
   * Display the specified resource.
   */
  public function show(ResultadoOperacao $resultado)
  {
    if (!$this->temPermissao($resultado)) {
      abort(403, 'Você não tem permissão para visualizar este resultado.');
    }

    $resultado->load(['operacao', 'user']);

    return Inertia::render('ResultadosOperacao/Show', [
      'resultado' => $resultado,
      'estatisticas' => $resultado->getEstatisticas(),
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(ResultadoOperacao $resultado)
  {
    if (!$this->temPermissao($resultado)) {
      abort(403, 'Você não tem permissão para editar este resultado.');
    }

    $resultado->load('operacao');

    return Inertia::render('ResultadosOperacao/Edit', [
      'resultado' => $resultado,
      'opcoes' => [
        'tipos_arma' => ResultadoOperacao::getTiposArma(),
        'tipos_entorpecente' => ResultadoOperacao::getTiposEntorpecente(),
      ],
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(
    ResultadoOperacaoRequest $request,
    ResultadoOperacao $resultado,
  ) {
    if (!$this->temPermissao($resultado)) {
      abort(403, 'Você não tem permissão para editar este resultado.');
    }

    $resultado->update($request->validated());

    return redirect()
      ->route('resultados-operacao.show', ['resultado' => $resultado->id])
      ->with('success', 'Resultado atualizado com sucesso!');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(ResultadoOperacao $resultado)
  {
    if (!$this->temPermissao($resultado)) {
      abort(403, 'Você não tem permissão para excluir este resultado.');
    }

    $resultado->delete();

    return redirect()
      ->route('resultados-operacao.index')
      ->with('success', 'Resultado excluído com sucesso!');
  }

  /**
   * Gerar PDF do resultado da operação
   */
  public function gerarPdf(ResultadoOperacao $resultado)
  {
    if (!$this->temPermissao($resultado)) {
      abort(403, 'Você não tem permissão para gerar PDF deste resultado.');
    }

    $resultado->load(['operacao', 'user']);

    $pdf = Pdf::loadView('resultados-operacao.pdf', [
      'resultado' => $resultado,
      'operacao' => $resultado->operacao,
      'estatisticas' => $resultado->getEstatisticas(),
    ]);

    $nomeArquivo =
      'Resultado_Operacao_' .
      str_replace(' ', '_', $resultado->operacao->nome_operacao) .
      '_' .
      now()->format('Y-m-d') .
      '.pdf';

    return $pdf->download($nomeArquivo);
  }

  /**
   * Dashboard com estatísticas (para Metabase)
   */
  public function dashboard(Request $request)
  {
    $user = auth()->user();
    $unidade = $user->lotacao;

    // Estatísticas gerais da unidade
    $totalResultados = ResultadoOperacao::porUnidade($unidade)->count();

    $totalPrisoes =
      ResultadoOperacao::porUnidade($unidade)->sum(
        'mandados_prisao_cumpridos',
      ) + ResultadoOperacao::porUnidade($unidade)->sum('prisoes_flagrante');

    $totalArmasApreendidas = ResultadoOperacao::porUnidade($unidade)->sum(
      'quantidade_armas_apreendidas',
    );

    $totalValoresApreendidos = ResultadoOperacao::porUnidade($unidade)->sum(
      'valores_dinheiro',
    );

    $resultadosComEntorpecentes = ResultadoOperacao::porUnidade($unidade)
      ->comEntorpecentes()
      ->count();

    // Estatísticas do mês atual
    $mesAtual = now()->startOfMonth();
    $mesFim = now()->endOfMonth();

    $resultadosMesAtual = ResultadoOperacao::porUnidade($unidade)
      ->porPeriodo($mesAtual, $mesFim)
      ->count();

    // Últimos resultados
    $ultimosResultados = ResultadoOperacao::porUnidade($unidade)
      ->with('operacao')
      ->orderBy('created_at', 'desc')
      ->limit(5)
      ->get();

    return Inertia::render('ResultadosOperacao/Dashboard', [
      'estatisticas' => [
        'total_resultados' => $totalResultados,
        'total_prisoes' => $totalPrisoes,
        'total_armas_apreendidas' => $totalArmasApreendidas,
        'total_valores_apreendidos' => $totalValoresApreendidos,
        'resultados_com_entorpecentes' => $resultadosComEntorpecentes,
        'resultados_mes_atual' => $resultadosMesAtual,
      ],
      'ultimos_resultados' => $ultimosResultados,
      'unidade' => $unidade,
    ]);
  }
}
