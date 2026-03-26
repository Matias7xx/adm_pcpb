<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportarNoticiasPlone extends Command
{
  protected $signature = 'noticias:importar-plone
                            {--url= : URL base do Plone (ex: https://portal.pb.gov.br)}
                            {--login= : Login de acesso à API do Plone}
                            {--password= : Senha de acesso à API do Plone}
                            {--path=noticias : Caminho das notícias no Plone (ex: noticias, noticias/ultimas)}
                            {--limit=0 : Limitar quantidade de notícias importadas (0 = todas)}
                            {--sleep=1 : Segundos de pausa entre cada notícia importada (0 = sem pausa)}
                            {--noticia-url= : Importar uma notícia específica pela URL do Plone}
                            {--dry-run : Simula a importação sem salvar nada no banco}';

  protected $description = 'Importa notícias do sistema Plone para o sistema PCPB';

  private string $baseUrl;
  private string $token;
  private int $importadas = 0;
  private int $ignoradas = 0;
  private int $erros = 0;

  public function handle(): int
  {
    $this->baseUrl = rtrim($this->option('url'), '/');
    $login = $this->option('login');
    $password = $this->option('password');
    $path = $this->option('path');
    $limit = (int) $this->option('limit');
    $sleep = max(0, (int) $this->option('sleep'));
    $noticiaUrl = $this->option('noticia-url');
    $dryRun = $this->option('dry-run');

    // Se não informou --url mas informou --noticia-url, extrai o base URL da própria URL
    if (!$this->baseUrl && $noticiaUrl) {
      $parsed = parse_url($noticiaUrl);
      $this->baseUrl = $parsed['scheme'] . '://' . $parsed['host'];
    }

    if (!$login || !$password) {
      $this->error('Informe --login e --password.');
      return self::FAILURE;
    }

    if (!$noticiaUrl && !$this->baseUrl) {
      $this->error('Informe --url, --login e --password.');
      $this->line('Exemplo:');
      $this->line(
        '  php artisan noticias:importar-plone --url=https://portal.pb.gov.br --login=admin --password=senha --path=noticias',
      );
      return self::FAILURE;
    }

    if ($dryRun) {
      $this->warn('MODO DRY-RUN: nenhum dado será salvo no banco.');
    }

    // 1. Autenticar
    $this->info('Autenticando na API do Plone...');
    if (!$this->autenticar($login, $password)) {
      return self::FAILURE;
    }
    $this->info('Autenticado com sucesso.');

    // Modo: importar notícia específica por URL
    if ($noticiaUrl) {
      $this->info("Importando notícia específica: {$noticiaUrl}");
      try {
        $this->importarNoticia(['@id' => $noticiaUrl], $dryRun);
        if ($this->importadas > 0) {
          $this->info('Notícia importada com sucesso!');
        } else {
          $this->warn('Notícia ignorada (já existia no banco).');
        }
      } catch (\Exception $e) {
        $this->error('Erro: ' . $e->getMessage());
        return self::FAILURE;
      }
      return self::SUCCESS;
    }

    // 2. Buscar todas as notícias via @search
    $this->info("Buscando notícias em /{$path}...");
    $items = $this->buscarTodasNoticias($path, $limit);

    if (empty($items)) {
      $this->warn('Nenhuma notícia encontrada.');
      return self::SUCCESS;
    }

    $total = count($items);
    $this->info("{$total} notícia(s) encontrada(s). Iniciando importação...\n");

    $bar = $this->output->createProgressBar($total);
    $bar->start();

    // 3. Importar cada notícia
    foreach ($items as $item) {
      try {
        $this->importarNoticia($item, $dryRun);
      } catch (\Exception $e) {
        $this->erros++;
        $this->newLine();
        $this->error("Erro em [{$item['@id']}]: " . $e->getMessage());
      }
      $bar->advance();
      if ($sleep > 0) {
        sleep($sleep);
      }
    }

    $bar->finish();
    $this->newLine(2);

    // 4. Resumo
    $this->table(
      ['Status', 'Quantidade'],
      [
        ['Importadas', $this->importadas],
        ['Ignoradas (já existiam)', $this->ignoradas],
        ['Erros', $this->erros],
        ['Total', $total],
      ],
    );

    return self::SUCCESS;
  }

  // Autenticação

  private function autenticar(string $login, string $password): bool
  {
    $response = Http::withHeaders([
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
    ])->post("{$this->baseUrl}/@login", [
      'login' => $login,
      'password' => $password,
    ]);

    if (!$response->successful()) {
      $this->error('Falha na autenticação: ' . $response->body());
      return false;
    }

    $this->token = $response->json('token');
    return true;
  }

  // Busca paginada

  private function buscarTodasNoticias(string $path, int $limit): array
  {
    $estrategia = $this->descobrirEstrategia($path);

    if ($estrategia === null) {
      return [];
    }

    $items = [];
    $bSize = 50;
    $bStart = 0;

    do {
      $params = array_merge($estrategia['params'], [
        'b_size' => $bSize,
        'b_start' => $bStart,
      ]);

      $response = $this->httpComRetry(
        fn() => Http::withHeaders([
          'Authorization' => "Bearer {$this->token}",
          'Accept' => 'application/json',
        ])
          ->timeout(60)
          ->get($estrategia['url'], $params),
      );

      if (!$response || !$response->successful()) {
        $this->error(
          'Erro ao buscar notícias: ' . ($response?->body() ?? 'timeout'),
        );
        break;
      }

      $data = $response->json();
      $batch = $data['items'] ?? [];
      $items = array_merge($items, $batch);
      $bStart += $bSize;

      if ($limit > 0 && count($items) >= $limit) {
        $items = array_slice($items, 0, $limit);
        break;
      }
    } while (count($batch) === $bSize);

    return $items;
  }

  private function descobrirEstrategia(string $path): ?array
  {
    $estrategias = [
      // Tipo encontrado
      [
        'nome' => '@search portal_type=collective.nitf.content + path',
        'url' => "{$this->baseUrl}/@search",
        'params' => [
          'portal_type' => 'collective.nitf.content',
          'path' => "/{$path}",
          'sort_on' => 'effective',
          'sort_order' => 'ascending',
        ],
      ],
      [
        'nome' => '@search portal_type=collective.nitf.content (sem path)',
        'url' => "{$this->baseUrl}/@search",
        'params' => [
          'portal_type' => 'collective.nitf.content',
          'sort_on' => 'effective',
          'sort_order' => 'ascending',
        ],
      ],
      // Fallbacks para outros portais Plone
      [
        'nome' => '@search portal_type=News Item + path',
        'url' => "{$this->baseUrl}/@search",
        'params' => [
          'portal_type' => 'News Item',
          'path' => "/{$path}",
          'sort_on' => 'effective',
          'sort_order' => 'ascending',
        ],
      ],
      [
        'nome' => '@search portal_type=News Item (sem path)',
        'url' => "{$this->baseUrl}/@search",
        'params' => [
          'portal_type' => 'News Item',
          'sort_on' => 'effective',
          'sort_order' => 'ascending',
        ],
      ],
      [
        'nome' => "pasta /{$path} diretamente",
        'url' => "{$this->baseUrl}/{$path}",
        'params' => ['sort_on' => 'effective', 'sort_order' => 'ascending'],
      ],
    ];

    foreach ($estrategias as $e) {
      $response = Http::withHeaders([
        'Authorization' => "Bearer {$this->token}",
        'Accept' => 'application/json',
      ])
        ->timeout(60)
        ->get(
          $e['url'],
          array_merge($e['params'], ['b_size' => 1, 'b_start' => 0]),
        );

      if (
        $response->successful() &&
        ($response->json('items_total') ?? 0) > 0
      ) {
        $this->info("  Estrategia de busca: {$e['nome']}");
        return $e;
      }
    }

    $this->warn(
      'Nenhuma estrategia retornou resultados. Verifique o --path informado.',
    );
    return null;
  }

  // Importar uma notícia

  private function importarNoticia(array $item, bool $dryRun): void
  {
    $ploneUrl = $item['@id'];

    // Buscar detalhes completos da notícia
    $response = $this->httpComRetry(
      fn() => Http::withHeaders([
        'Authorization' => "Bearer {$this->token}",
        'Accept' => 'application/json',
      ])
        ->timeout(60)
        ->get($ploneUrl),
    );

    if (!$response || !$response->successful()) {
      throw new \Exception(
        'HTTP ' . ($response?->status() ?? 'timeout') . ' ao buscar detalhes',
      );
    }

    $data = $response->json();

    $titulo = $data['title'] ?? null;

    if (!$titulo) {
      $this->ignoradas++;
      return;
    }

    // Verificar se já existe pelo título (evita duplicatas em re-execuções)
    if (Noticia::where('titulo', $titulo)->exists()) {
      $this->ignoradas++;
      return;
    }

    // Mapear data de publicação: tenta effective, fallback para created, fallback para hoje
    $dataPublicacao = null;
    foreach (['effective', 'created'] as $campoData) {
      if (!empty($data[$campoData])) {
        try {
          $parsed = Carbon::parse($data[$campoData]);
          // Ignorar datas epoch (Plone usa 1969-12-31 quando não preenchido)
          if ($parsed->year > 1970) {
            $dataPublicacao = $parsed->format('Y-m-d');
            break;
          }
        } catch (\Exception) {
          // continua para o próximo campo
        }
      }
    }
    $dataPublicacao ??= now()->format('Y-m-d');

    // Slug da notícia — usado para montar os caminhos de storage
    $slug = $this->sanitizeFolderName($titulo);

    // Descrição curta
    $descricaoCurta = $data['description'] ?? null;

    // Conteúdo HTML
    $conteudo = $data['text']['data'] ?? null;
    $imagemPath = null;

    if (!$dryRun) {
      // 1. Baixar e reescrever imagens do corpo
      //    Cada <img> é salva em noticias/{slug}/content/ e o src é substituído pela URL local
      if ($conteudo) {
        $conteudo = $this->processarImagensCorpo($conteudo, $slug);
      }

      // 2. Definir imagem de capa
      if (!empty($data['image'])) {
        // Campo image dedicado (lead image)
        $imagemPath = $this->baixarImagemCapa($data['image'], $slug);
      } elseif (!empty($data['items'])) {
        // Notícia é uma pasta — imagens ficam como itens filhos do tipo Image
        $imagensInjetadas = '';
        foreach ($data['items'] as $childItem) {
          if (($childItem['@type'] ?? '') === 'Image') {
            $imageUrl = rtrim($childItem['@id'], '/') . '/@@images/image';
            $urlLocal = $this->baixarImagemCorpo($imageUrl, $slug);

            if ($urlLocal) {
              $imagensInjetadas .= "<p><img src=\"{$urlLocal}\" alt=\"{$titulo}\"></p>\n";
            }
          }
        }

        if ($imagensInjetadas) {
          $conteudo = $imagensInjetadas . ($conteudo ?? '');
        }
      }

      // Fallback: copia primeira imagem do corpo como capa
      if (!$imagemPath && $conteudo) {
        $imagemPath = $this->copiarPrimeiraImagemDoCorpoComoCapa(
          $conteudo,
          $slug,
        );
      }
    }

    Noticia::create([
      'titulo' => $titulo,
      'descricao_curta' => $descricaoCurta,
      'conteudo' => $conteudo,
      'imagem' => $imagemPath,
      'status' => 'publicado',
      'data_publicacao' => $dataPublicacao,
      'destaque' => false,
      'visualizacoes' => 0,
    ]);

    $this->importadas++;
  }

  // Imagem de capa dedicada (lead image do Plone)
  // Salva em: noticias/{slug}/capa/capa.{ext}

  private function baixarImagemCapa(array $imagemData, string $slug): ?string
  {
    $downloadUrl =
      $imagemData['download'] ??
      ($imagemData['scales']['large']['download'] ??
        ($imagemData['scales']['preview']['download'] ?? null));

    if (!$downloadUrl) {
      return null;
    }

    try {
      $response = $this->httpComRetry(
        fn() => Http::withHeaders([
          'Authorization' => "Bearer {$this->token}",
        ])
          ->timeout(60)
          ->get($downloadUrl),
      );

      if (!$response || !$response->successful()) {
        $this->newLine();
        $this->warn("  [CAPA] Falha ao baixar: {$downloadUrl}");
        $this->warn(
          '  [CAPA] Motivo: ' .
            ($response
              ? "HTTP {$response->status()}"
              : 'timeout após 3 tentativas'),
        );
        return null;
      }

      $extension = $this->extensaoPorMimeType(
        $response->header('Content-Type'),
      );
      $path = "noticias/{$slug}/capa/capa{$extension}";

      Storage::disk('public')->put($path, $response->body());

      return $path;
    } catch (\Exception $e) {
      $this->newLine();
      $this->warn('  [CAPA] Exceção: ' . $e->getMessage());
      return null;
    }
  }

  // Imagens do corpo
  // Baixa cada <img> e salva em: noticias/{slug}/content/{uuid}.{ext}
  // Reescreve o src no HTML para a URL pública local

  private function processarImagensCorpo(string $html, string $slug): string
  {
    $cache = []; // URL original → caminho local já baixado

    return preg_replace_callback(
      '/<img([^>]*?)src=["\']([^"\']+)["\']([^>]*?)>/i',
      function (array $matches) use ($slug, &$cache) {
        $antes = $matches[1];
        $srcOriginal = $matches[2];
        $depois = $matches[3];

        // Reutiliza se já baixou essa URL nessa notícia
        if (isset($cache[$srcOriginal])) {
          return "<img{$antes}src=\"{$cache[$srcOriginal]}\"{$depois}>";
        }

        $novoSrc = $this->baixarImagemCorpo($srcOriginal, $slug);

        if ($novoSrc) {
          $cache[$srcOriginal] = $novoSrc;
        }

        $src = $novoSrc ?? $srcOriginal;
        return "<img{$antes}src=\"{$src}\"{$depois}>";
      },
      $html,
    );
  }

  private function baixarImagemCorpo(string $url, string $slug): ?string
  {
    // Completar URLs relativas com o base URL do Plone
    if (str_starts_with($url, '/')) {
      $url = $this->baseUrl . $url;
    }

    // Ignorar URLs que não sejam http (ex: data:image/...)
    if (!str_starts_with($url, 'http')) {
      return null;
    }

    try {
      $response = $this->httpComRetry(
        fn() => Http::withHeaders([
          'Authorization' => "Bearer {$this->token}",
        ])
          ->timeout(60)
          ->get($url),
      );

      if (!$response || !$response->successful()) {
        $this->newLine();
        $this->warn("  [CORPO] Falha ao baixar: {$url}");
        $this->warn(
          '  [CORPO] Motivo: ' .
            ($response
              ? "HTTP {$response->status()}"
              : 'timeout após 3 tentativas'),
        );
        return null;
      }

      $extension = $this->extensaoPorMimeType(
        $response->header('Content-Type'),
      );
      $filename = Str::uuid() . $extension;

      // Salva em noticias/{slug}/content/ — mesma estrutura usada pelo CKEditor
      $path = "noticias/{$slug}/content/{$filename}";

      Storage::disk('public')->put($path, $response->body());

      // Retorna caminho relativo para funcionar em qualquer ambiente
      return '/storage/' . $path;
    } catch (\Exception $e) {
      $this->newLine();
      $this->warn("  [CORPO] Exceção ao baixar: {$url}");
      $this->warn('  [CORPO] Erro: ' . $e->getMessage());
      return null;
    }
  }

  // Fallback de capa: copia a 1ª imagem do corpo (já salva localmente) para a pasta capa.
  // O src no corpo NÃO é alterado — a imagem fica nos dois lugares.
  // Salva em: noticias/{slug}/capa/capa.{ext}

  private function copiarPrimeiraImagemDoCorpoComoCapa(
    string $html,
    string $slug,
  ): ?string {
    // Pega o src já reescrito (URL pública local) da primeira <img>
    if (!preg_match('/<img[^>]*?src=["\']([^"\']+)["\'][^>]*?>/i', $html, $m)) {
      return null;
    }

    $urlLocal = $m[1];

    // Extrai o caminho relativo dentro do storage público
    // Ex: http://app.local/storage/noticias/slug/content/uuid.jpg
    //  -> noticias/slug/content/uuid.jpg
    $parsedPath = parse_url($urlLocal, PHP_URL_PATH);
    $relativePath = preg_replace('#^/storage/#', '', $parsedPath);

    if (!Storage::disk('public')->exists($relativePath)) {
      return null;
    }

    try {
      $extension = '.' . pathinfo($relativePath, PATHINFO_EXTENSION);
      $destination = "noticias/{$slug}/capa/capa{$extension}";

      Storage::disk('public')->copy($relativePath, $destination);

      return $destination;
    } catch (\Exception) {
      return null;
    }
  }

  // Utilitários

  /**
   * Executa uma chamada HTTP com até 3 tentativas em caso de timeout ou erro de conexão.
   * Aguarda 5 segundos entre cada tentativa.
   */
  private function httpComRetry(
    callable $chamada,
    int $tentativas = 3,
  ): ?\Illuminate\Http\Client\Response {
    for ($i = 1; $i <= $tentativas; $i++) {
      try {
        $response = $chamada();
        if ($response->successful()) {
          return $response;
        }
        // Erro HTTP (4xx/5xx) — não tenta de novo
        return $response;
      } catch (\Illuminate\Http\Client\ConnectionException $e) {
        $this->newLine();
        $this->warn(
          "  Timeout/conexão (tentativa {$i}/{$tentativas}): {$e->getMessage()}",
        );
        if ($i < $tentativas) {
          $this->line('  Aguardando 5s antes de tentar novamente...');
          sleep(5);
        }
      }
    }
    return null;
  }

  private function extensaoPorMimeType(string $mimeType): string
  {
    return match (true) {
      str_contains($mimeType, 'jpeg') => '.jpg',
      str_contains($mimeType, 'png') => '.png',
      str_contains($mimeType, 'gif') => '.gif',
      str_contains($mimeType, 'webp') => '.webp',
      default => '.jpg',
    };
  }

  /**
   * Replica a lógica do UploadHelper::sanitizeFolderName() para garantir que os caminhos
   * gerados pelo comando sejam idênticos aos gerados pelo painel admin.
   */
  private function sanitizeFolderName(string $name): string
  {
    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
    $name = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $name);
    $name = preg_replace('/\s+/', '-', $name);
    $name = preg_replace('/-+/', '-', $name);
    $name = trim($name, '-');

    return strtolower($name ?: 'sem-titulo');
  }
}
