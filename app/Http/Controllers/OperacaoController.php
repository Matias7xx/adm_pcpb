<?php

namespace App\Http\Controllers;

use App\Http\Requests\OperacaoRequest;
use App\Mail\NovaOperacao;
use App\Models\Operacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OperacaoController extends Controller
{
  /**
   * Verifica permissão de forma case-insensitive e sem espaços
   */
  private function temPermissao(Operacao $operacao): bool
  {
    $lotacao = auth()->user()->lotacao;

    if (!$lotacao) {
      return false;
    }

    return mb_strtolower(
      trim($operacao->unidade_policial_responsavel ?? ''),
    ) === mb_strtolower(trim($lotacao));
  }

  private function operacoesVencidasSemResultado(string $lotacao)
  {
    return Operacao::where('unidade_policial_responsavel', $lotacao)
      ->where('data_operacao', '<', now()->subHours(72)->toDateString())
      ->whereDoesntHave('resultado')
      ->orderBy('data_operacao', 'asc')
      ->get(['id', 'nome_operacao', 'data_operacao']);
  }

  //Bloqueia o EDIT da operação vencida
  private function estaVencidaSemResultado(Operacao $operacao): bool
  {
    return $operacao->data_operacao < now()->subHours(72)->toDateString() &&
      !$operacao->resultado()->exists();
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user = auth()->user();

    // Filtros
    $query = Operacao::with(['user', 'resultado'])
      ->where('unidade_policial_responsavel', $user->lotacao)
      ->orderBy('data_operacao', 'desc');

    if ($request->filled('data_inicio')) {
      if ($request->filled('data_fim')) {
        // Se ambos preenchidos: range
        $query->whereBetween('data_operacao', [
          $request->data_inicio,
          $request->data_fim,
        ]);
      } else {
        // Se só data_inicio: data exata
        $query->whereDate('data_operacao', $request->data_inicio);
      }
    }

    if ($request->filled('origem')) {
      $query->where('origem_operacao', $request->origem);
    }

    if ($request->filled('busca')) {
      $busca = mb_strtolower($request->busca, 'UTF-8'); // Converte o termo de busca para minúsculo

      $query->where(function ($q) use ($busca) {
        $q->whereRaw('LOWER(nome_operacao) LIKE ?', ["%{$busca}%"])
          ->orWhereRaw('LOWER(autoridade_responsavel_nome) LIKE ?', [
            "%{$busca}%",
          ])
          ->orWhereRaw('LOWER(cidades_alvo) LIKE ?', ["%{$busca}%"]);
      });
    }

    if ($request->filled('uf_alvo')) {
      // Para objeto JSON {"RN": 2, "PE": 1}, o operador ? do PostgreSQL
      // verifica se a chave existe. No PDO, ? precisa ser escapado como ??.
      $query->whereRaw('ufs_alvo_outros_estados ?? ?', [$request->uf_alvo]);
    }

    $operacoes = $query->paginate(10);

    $vencidas = $this->operacoesVencidasSemResultado($user->lotacao);

    return Inertia::render('Operacoes/Index', [
      'operacoes' => $operacoes,
      'filtros' => $request->only([
        'data_inicio',
        'data_fim',
        'origem',
        'busca',
        'uf_alvo',
      ]),
      'bloqueado' => $vencidas->isNotEmpty(),
      'operacoes_vencidas' => $vencidas,
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $vencidas = $this->operacoesVencidasSemResultado(auth()->user()->lotacao);
    if ($vencidas->isNotEmpty()) {
      return redirect()
        ->route('operacoes.index')
        ->with(
          'error',
          'Regularize os resultados pendentes antes de criar uma nova operação.',
        );
    }

    return Inertia::render('Operacoes/Create', [
      'opcoes' => [
        'origens' => Operacao::getOrigensOperacao(),
        'ufs' => Operacao::getUnidadesFederativas(),
        'unidades' => Operacao::getUnidadesVinculadas(),
        'unidades_especializadas' => Operacao::getUnidadesEspecializadas(),
        'delegacias' => Operacao::getDelegaciasSeccionais(),
      ],
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(OperacaoRequest $request)
  {
    $user = auth()->user();

    // Proteção contra bypass via API
    $vencidas = $this->operacoesVencidasSemResultado($user->lotacao);
    if ($vencidas->isNotEmpty()) {
      return redirect()
        ->route('operacoes.index')
        ->with(
          'error',
          'Regularize os resultados pendentes antes de criar uma nova operação.',
        );
    }

    // Preencher dados automáticos do usuário logado
    $dados = $request->validated();
    $dados['user_id'] = $user->id;
    $dados['policial_responsavel_nome'] = $user->name;
    $dados['policial_responsavel_matricula'] = $user->matricula;
    $dados['unidade_policial_responsavel'] = $user->lotacao;

    $operacao = Operacao::create($dados);

    // Notificar a DIOP por e-mail
    $diopEmail = config('mail.diop_email', env('DIOP_EMAIL'));
    if ($diopEmail) {
      try {
        Mail::to($diopEmail)->send(new NovaOperacao($operacao));
      } catch (\Exception $e) {
        Log::error('Falha ao enviar e-mail de nova operação para DIOP', [
          'operacao_id' => $operacao->id,
          'error' => $e->getMessage(),
        ]);
      }
    }

    // Passar o ID explicitamente ao invés do objeto
    return redirect()
      ->route('operacoes.show', ['operacao' => $operacao->id])
      ->with('success', 'Operação cadastrada com sucesso!');
  }

  /**
   * Display the specified resource.
   */
  public function show(Operacao $operacao)
  {
    if (!$this->temPermissao($operacao)) {
      abort(403, 'Você não tem permissão para visualizar esta operação.');
    }

    // Carregar relacionamento com resultado
    $operacao->load(['user', 'resultado']);
    $vencidas = $this->operacoesVencidasSemResultado(auth()->user()->lotacao);

    return Inertia::render('Operacoes/Show', [
      'operacao' => $operacao,
      'estatisticas' => $operacao->getEstatisticas(),
      'bloqueado' => $this->estaVencidaSemResultado($operacao),
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Operacao $operacao)
  {
    if (!$this->temPermissao($operacao)) {
      abort(403, 'Você não tem permissão para editar esta operação.');
    }

    // Bloqueia edição apenas se ESTA operação está vencida sem resultado. Para desabilitar é só comentar
    if ($this->estaVencidaSemResultado($operacao)) {
      return redirect()
        ->route('operacoes.show', $operacao->id)
        ->with(
          'error',
          'Cadastre o resultado desta operação antes de editá-la.',
        );
    }

    return Inertia::render('Operacoes/Edit', [
      'operacao' => $operacao,
      'opcoes' => [
        'origens' => Operacao::getOrigensOperacao(),
        'ufs' => Operacao::getUnidadesFederativas(),
        'unidades' => Operacao::getUnidadesVinculadas(),
        'unidades_especializadas' => Operacao::getUnidadesEspecializadas(),
        'delegacias' => Operacao::getDelegaciasSeccionais(),
      ],
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(OperacaoRequest $request, Operacao $operacao)
  {
    if (!$this->temPermissao($operacao)) {
      abort(403, 'Você não tem permissão para editar esta operação.');
    }

    // Bloqueia edição apenas se ESTA operação está vencida sem resultado. Para desabilitar é só comentar
    if ($this->estaVencidaSemResultado($operacao)) {
      return redirect()
        ->route('operacoes.show', $operacao->id)
        ->with(
          'error',
          'Cadastre o resultado desta operação antes de editá-la.',
        );
    }

    $dados = $request->validated();

    // Extrai a justificativa — não persiste no modelo, vai para a auditoria
    $justificativa = $dados['justificativa_edicao'];
    unset($dados['justificativa_edicao']);

    // Injeta no auditExtra para que a Auditable trait inclua no dados_novos do banco
    $operacao->auditExtra = ['_justificativa_edicao' => $justificativa];

    $operacao->update($dados);

    // Passar o ID explicitamente
    return redirect()
      ->route('operacoes.show', ['operacao' => $operacao->id])
      ->with('success', 'Operação atualizada com sucesso!');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Operacao $operacao)
  {
    if (!$this->temPermissao($operacao)) {
      abort(403, 'Você não tem permissão para excluir esta operação.');
    }

    $operacao->delete();

    return redirect()
      ->route('operacoes.index')
      ->with('success', 'Operação excluída com sucesso!');
  }

  /**
   * Gerar PDF da operação
   */
  public function gerarPdf(Operacao $operacao)
  {
    if (!$this->temPermissao($operacao)) {
      abort(403, 'Você não tem permissão para gerar PDF desta operação.');
    }

    $operacao->load('user');

    $pdf = Pdf::loadView('operacoes.pdf', [
      'operacao' => $operacao,
      'estatisticas' => $operacao->getEstatisticas(),
    ]);

    $nomeArquivo =
      'Operacao_' .
      str_replace(' ', '_', $operacao->nome_operacao) .
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
    $totalOperacoes = Operacao::porUnidade($unidade)->count();

    $operacoesNacional = Operacao::porUnidade($unidade)
      ->where('origem_operacao', 'Nacional')
      ->count();

    $operacoesEstadual = Operacao::porUnidade($unidade)
      ->where('origem_operacao', 'Estadual')
      ->count();

    $operacoesApoio = Operacao::porUnidade($unidade)
      ->where('origem_operacao', 'Apoio a outro Estado')
      ->count();

    // Estatísticas do mês atual
    $mesAtual = now()->startOfMonth();
    $mesFim = now()->endOfMonth();

    $operacoesMesAtual = Operacao::porUnidade($unidade)
      ->porPeriodo($mesAtual, $mesFim)
      ->count();

    // Últimas operações
    $ultimasOperacoes = Operacao::porUnidade($unidade)
      ->orderBy('data_operacao', 'desc')
      ->limit(5)
      ->get();

    return Inertia::render('Operacoes/Dashboard', [
      'estatisticas' => [
        'total_operacoes' => $totalOperacoes,
        'operacoes_nacional' => $operacoesNacional,
        'operacoes_estadual' => $operacoesEstadual,
        'operacoes_apoio' => $operacoesApoio,
        'operacoes_mes_atual' => $operacoesMesAtual,
      ],
      'ultimas_operacoes' => $ultimasOperacoes,
      'unidade' => $unidade,
    ]);
  }
}
