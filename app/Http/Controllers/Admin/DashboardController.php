<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Noticia;
use App\Models\NoticiaView;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
  /**
   * Exibir o dashboard administrativo
   */
  public function index()
  {
    $dadosDashboard = $this->obterMetricasNoticias();
    $dadosGrafico = $this->obterDadosGrafico();

    return Inertia::render('Admin/Dashboard', [
      'dashboardData' => $dadosDashboard,
      'chartData' => $dadosGrafico,
      'resumoMeses' => $this->obterResumoPorMes(),
    ]);
  }

  /**
   * Métricas de notícias
   */
  private function obterMetricasNoticias(): array
  {
    $mesAtual = Carbon::now()->startOfMonth();
    $mesPassado = Carbon::now()->subMonth()->startOfMonth();

    $total = Noticia::count();
    $publicadas = Noticia::where('status', 'publicado')->count();
    $rascunhos = Noticia::where('status', 'rascunho')->count();
    $arquivadas = Noticia::where('status', 'arquivado')->count();
    $destaques = Noticia::where('destaque', true)
      ->where('status', 'publicado')
      ->count();

    $totalVisualizacoes = (int) Noticia::where('status', 'publicado')->sum(
      'visualizacoes',
    );

    $noticiasMesAtual = Noticia::where('status', 'publicado')
      ->whereYear('data_publicacao', $mesAtual->year)
      ->whereMonth('data_publicacao', $mesAtual->month)
      ->count();
    $visualizacoesMes = NoticiaView::whereYear('viewed_at', $mesAtual->year)
      ->whereMonth('viewed_at', $mesAtual->month)
      ->count();
    $noticiasMesPassado = Noticia::where('status', 'publicado')
      ->whereYear('data_publicacao', $mesPassado->year)
      ->whereMonth('data_publicacao', $mesPassado->month)
      ->count();

    $percentual = $this->calcularPercentual(
      $noticiasMesAtual,
      $noticiasMesPassado,
    );

    // Notícia mais visualizada do mês
    $maisVisualizadaMes = Noticia::where('status', 'publicado')
      ->whereYear('data_publicacao', $mesAtual->year)
      ->whereMonth('data_publicacao', $mesAtual->month)
      ->orderByDesc('visualizacoes')
      ->first(['titulo', 'visualizacoes']);

    // Últimas 5 notícias publicadas
    $ultimasPublicadas = Noticia::where('status', 'publicado')
      ->orderByDesc('data_publicacao')
      ->take(5)
      ->get(['id', 'titulo', 'visualizacoes', 'destaque', 'data_publicacao']);

    return [
      'total' => $total,
      'publicadas' => $publicadas,
      'rascunhos' => $rascunhos,
      'arquivadas' => $arquivadas,
      'destaques' => $destaques,
      'total_visualizacoes' => $totalVisualizacoes,
      'novas_mes' => $noticiasMesAtual,
      'visualizacoes_mes' => $visualizacoesMes,
      'trend' => $percentual >= 0 ? 'up' : 'down',
      'percentage' => abs($percentual),
      'mais_visualizada_mes' => $maisVisualizadaMes
        ? [
          'titulo' => $maisVisualizadaMes->titulo,
          'visualizacoes' => $maisVisualizadaMes->visualizacoes,
        ]
        : null,
      'ultimas_publicadas' => $ultimasPublicadas
        ->map(
          fn($n) => [
            'id' => $n->id,
            'titulo' => $n->titulo,
            'visualizacoes' => $n->visualizacoes,
            'destaque' => $n->destaque,
            'data_publicacao' => $n->data_publicacao?->format('d/m/Y'),
          ],
        )
        ->toArray(),
    ];
  }

  /**
   * Dados do gráfico — publicações por mês (últimos 6 meses)
   */
  private function obterDadosGrafico(): array
  {
    $labels = [];
    $publicadas = [];
    $visualizacoes = [];

    for ($i = 5; $i >= 0; $i--) {
      $mes = Carbon::now()->subMonths($i);
      $inicio = $mes->copy()->startOfMonth();
      $fim = $mes->copy()->endOfMonth();

      $labels[] = $mes->translatedFormat('M/Y');
      $publicadas[] = Noticia::where('status', 'publicado')
        ->whereBetween('data_publicacao', [$inicio, $fim])
        ->count();
      $visualizacoes[] = NoticiaView::whereBetween('viewed_at', [
        $inicio,
        $fim,
      ])->count();
    }

    return [
      'labels' => $labels,
      'datasets' => [
        [
          'label' => 'Notícias Publicadas',
          'data' => $publicadas,
          'borderColor' => '#3B82F6',
          'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
          'tension' => 0.4,
          'fill' => true,
          'yAxisID' => 'y',
        ],
        [
          'label' => 'Visualizações',
          'data' => $visualizacoes,
          'borderColor' => '#10B981',
          'backgroundColor' => 'rgba(16, 185, 129, 0.05)',
          'tension' => 0.4,
          'fill' => false,
          'yAxisID' => 'y1',
          'borderDash' => [6, 3],
          'pointRadius' => 5,
          'pointBackgroundColor' => '#10B981',
        ],
      ],
    ];
  }

  /**
   * Resumo dos últimos 6 meses (exceto o atual)
   */
  private function obterResumoPorMes(): array
  {
    $meses = [];

    for ($i = 5; $i >= 1; $i--) {
      $mes = Carbon::now()->subMonths($i);
      $inicio = $mes->copy()->startOfMonth();
      $fim = $mes->copy()->endOfMonth();

      $publicadas = Noticia::where('status', 'publicado')
        ->whereYear('data_publicacao', $mes->year)
        ->whereMonth('data_publicacao', $mes->month)
        ->count();
      $visualizacoes = NoticiaView::whereYear('viewed_at', $mes->year)
        ->whereMonth('viewed_at', $mes->month)
        ->count();

      $meses[] = [
        'mes' => $mes->translatedFormat('F/Y'),
        'mes_curto' => $mes->translatedFormat('M/Y'),
        'publicadas' => $publicadas,
        'visualizacoes' => $visualizacoes,
      ];
    }

    return $meses;
  }

  /**
   * Calcular percentual de variação
   */
  private function calcularPercentual(int $atual, int $anterior): float
  {
    if ($anterior === 0) {
      return $atual > 0 ? 100 : 0;
    }
    return round((($atual - $anterior) / $anterior) * 100, 1);
  }
}
