<?php

namespace App\Http\Controllers;

use App\Http\Requests\OperacaoRequest;
use App\Models\Operacao;
use Illuminate\Http\Request;
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

        return mb_strtolower(trim($operacao->unidade_policial_responsavel ?? ''))
            === mb_strtolower(trim($lotacao));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Filtros
        $query = Operacao::with('user')
            ->where('unidade_policial_responsavel', $user->lotacao)
            ->orderBy('data_operacao', 'desc');

        if ($request->filled('data_inicio')) {
            $query->where('data_operacao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data_operacao', '<=', $request->data_fim);
        }

        if ($request->filled('origem')) {
            $query->where('origem_operacao', $request->origem);
        }

        if ($request->filled('busca')) {
            $busca = mb_strtolower($request->busca, 'UTF-8'); // Converte o termo de busca para minúsculo
            
            $query->where(function($q) use ($busca) {
                $q->whereRaw('LOWER(nome_operacao) LIKE ?', ["%{$busca}%"])
                ->orWhereRaw('LOWER(autoridade_responsavel_nome) LIKE ?', ["%{$busca}%"])
                ->orWhereRaw('LOWER(cidades_alvo) LIKE ?', ["%{$busca}%"]);
            });
        }

        $operacoes = $query->paginate(10);

        return Inertia::render('Operacoes/Index', [
            'operacoes' => $operacoes,
            'filtros' => $request->only(['data_inicio', 'data_fim', 'origem', 'busca']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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

        // Preencher dados automáticos do usuário logado
        $dados = $request->validated();
        $dados['user_id'] = $user->id;
        $dados['policial_responsavel_nome'] = $user->name;
        $dados['policial_responsavel_matricula'] = $user->matricula;
        $dados['unidade_policial_responsavel'] = $user->lotacao;

        $operacao = Operacao::create($dados);

        // Passar o ID explicitamente ao invés do objeto
        return redirect()->route('operacoes.show', ['operacao' => $operacao->id])
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

        $operacao->load('user');

        return Inertia::render('Operacoes/Show', [
            'operacao' => $operacao,
            'estatisticas' => $operacao->getEstatisticas(),
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

        $operacao->update($request->validated());

        // Passar o ID explicitamente
        return redirect()->route('operacoes.show', ['operacao' => $operacao->id])
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

        return redirect()->route('operacoes.index')
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

        $nomeArquivo = 'Operacao_' . str_replace(' ', '_', $operacao->nome_operacao) . '_' . now()->format('Y-m-d') . '.pdf';

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