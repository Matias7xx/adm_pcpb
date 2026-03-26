<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use Illuminate\Console\Command;

class ExportarNoticiasCSV extends Command
{
  protected $signature = 'noticias:exportar-csv
                            {--output= : Caminho do arquivo CSV de saída (padrão: storage/app/noticias_export.csv)}';

  protected $description = 'Exporta todas as notícias publicadas para um arquivo CSV';

  public function handle(): int
  {
    $outputPath =
      $this->option('output') ?? storage_path('app/noticias_export.csv');

    $this->info('Buscando notícias publicadas...');

    $noticias = Noticia::where('status', 'publicado')
      ->whereNull('deleted_at')
      ->orderBy('data_publicacao', 'asc')
      ->orderBy('id', 'asc')
      ->get([
        'id',
        'titulo',
        'descricao_curta',
        'conteudo',
        'imagem',
        'carousel_images',
        'destaque',
        'data_publicacao',
        'status',
        'visualizacoes',
        'ordem_destaque',
        'created_at',
        'updated_at',
      ]);

    if ($noticias->isEmpty()) {
      $this->warn('Nenhuma notícia publicada encontrada.');
      return self::SUCCESS;
    }

    $total = $noticias->count();
    $this->info("{$total} notícia(s) encontrada(s). Gerando CSV...");

    $arquivo = fopen($outputPath, 'w');

    // BOM UTF-8 para compatibilidade com Excel
    fprintf($arquivo, chr(0xef) . chr(0xbb) . chr(0xbf));

    // Cabeçalho
    fputcsv($arquivo, [
      'id',
      'titulo',
      'descricao_curta',
      'conteudo',
      'imagem',
      'carousel_images',
      'destaque',
      'data_publicacao',
      'status',
      'visualizacoes',
      'ordem_destaque',
      'created_at',
      'updated_at',
    ], ';');

    $bar = $this->output->createProgressBar($total);
    $bar->start();

    foreach ($noticias as $noticia) {
      fputcsv($arquivo, [
        $noticia->id,
        $noticia->titulo,
        $noticia->descricao_curta,
        $noticia->conteudo,
        // Salva o caminho raw do banco (sem o accessor que adiciona /storage/)
        $noticia->getRawOriginal('imagem'),
        // carousel_images é array — serializa como JSON
        $noticia->carousel_images
          ? json_encode($noticia->carousel_images, JSON_UNESCAPED_UNICODE)
          : null,
        $noticia->destaque ? '1' : '0',
        $noticia->data_publicacao?->format('Y-m-d'),
        $noticia->status,
        $noticia->visualizacoes,
        $noticia->ordem_destaque,
        $noticia->created_at?->toISOString(),
        $noticia->updated_at?->toISOString(),
      ], ';');

      $bar->advance();
    }

    fclose($arquivo);
    $bar->finish();
    $this->newLine(2);

    $this->info("CSV gerado com sucesso em: {$outputPath}");
    $this->line("Total de notícias exportadas: {$total}");

    return self::SUCCESS;
  }
}