<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DiagnosticarPlone extends Command
{
  protected $signature = 'noticias:diagnosticar-plone
                            {--url=https://www.policiacivil.pb.gov.br : URL base do portal Plone}
                            {--login= : Login da API Plone}
                            {--password= : Senha da API Plone}
                            {--path=noticias : Caminho das notícias no Plone}
                            {--quantidade=3 : Quantas notícias testar (máx. 10)}';

  protected $description = 'Diagnostica a conexão com a API Plone e exibe como os dados seriam importados';

  private string $baseUrl;
  private ?string $token = null;

  public function handle(): int
  {
    $this->baseUrl = rtrim($this->option('url'), '/');
    $quantidade = min((int) $this->option('quantidade'), 10);

    $this->newLine();
    $this->line('  <fg=cyan;options=bold> Diagnóstico da API Plone — PCPB</>');
    $this->line('  Portal: <fg=yellow>' . $this->baseUrl . '</>');
    $this->newLine();

    // Autenticação
    $this->line('<options=bold>[ 1 ] Testando autenticação...</>');

    $login = $this->option('login') ?? $this->ask('Login da API Plone');
    $password =
      $this->option('password') ?? $this->secret('Senha da API Plone');

    if (!$this->autenticar($login, $password)) {
      return self::FAILURE;
    }

    $this->line('  <fg=green>✔ Token obtido com sucesso!</>');
    $this->line(
      '  Token (primeiros 40 chars): <fg=gray>' .
        substr($this->token, 0, 40) .
        '...</>',
    );
    $this->newLine();

    // Listagem de notícias
    $path = trim($this->option('path'), '/');
    $this->line(
      '<options=bold>[ 2 ] Buscando notícias em /' . $path . '...</>',
    );

    $resultados = $this->buscarNoticias($path, $quantidade);

    if ($resultados === null) {
      return self::FAILURE;
    }

    $total = $resultados['items_total'] ?? 0;
    $itens = $resultados['items'] ?? [];

    $this->line(
      "  <fg=green>✔ API respondeu!</> Total de notícias no Plone: <fg=yellow>{$total}</>",
    );
    $this->line(
      '  Analisando ' . count($itens) . ' notícia(s) como amostra...',
    );
    $this->newLine();

    if (empty($itens)) {
      $this->warn('  Nenhum item retornado. Verifique o --path informado.');
      return self::FAILURE;
    }

    // Detalhes de cada notícia
    $this->line(
      '<options=bold>[ 3 ] Mapeamento Plone → Noticia (banco de dados)</>',
    );
    $this->newLine();

    foreach ($itens as $index => $item) {
      $num = $index + 1;
      $this->line(
        "  <fg=cyan;options=bold>─── Notícia #{$num} ───────────────────────────────────────</>",
      );

      $detalhe = $this->buscarDetalhe($item['@id']);

      if (!$detalhe) {
        $this->line(
          '  <fg=red>✘ Não foi possível buscar os detalhes deste item.</>',
        );
        $this->newLine();
        continue;
      }

      $mapeado = $this->mapearCampos($detalhe);

      // Exibir campos do Plone
      $this->line('  <fg=white;options=bold>Plone (raw):</>');
      $this->table(
        ['Campo Plone', 'Valor'],
        [
          ['@id', $detalhe['@id'] ?? '—'],
          ['title', $this->truncar($detalhe['title'] ?? '—')],
          ['description', $this->truncar($detalhe['description'] ?? '—')],
          [
            'text.data',
            $this->truncar(strip_tags($detalhe['text']['data'] ?? '—'), 80),
          ],
          ['effective', $detalhe['effective'] ?? '—'],
          ['review_state', $detalhe['review_state'] ?? '—'],
          [
            'image',
            isset($detalhe['image'])
              ? '✔ Presente (' .
                ($detalhe['image']['content-type'] ?? '?') .
                ')'
              : '✘ Sem imagem',
          ],
        ],
      );

      // Exibir mapeamento para o model
      $this->line(
        '  <fg=white;options=bold>Noticia (model) — o que seria salvo:</>',
      );
      $this->table(
        ['Campo', 'Valor'],
        [
          ['titulo', $this->truncar($mapeado['titulo'])],
          ['descricao_curta', $this->truncar($mapeado['descricao_curta'])],
          ['conteudo', $this->truncar(strip_tags($mapeado['conteudo']), 80)],
          ['imagem', $mapeado['imagem'] ?? '(sem imagem)'],
          ['data_publicacao', $mapeado['data_publicacao']],
          ['status', $mapeado['status']],
        ],
      );

      // Verificar se imagem pode ser baixada
      if (isset($detalhe['image']['download'])) {
        $this->line(
          '  <fg=white;options=bold>Teste de download da imagem de capa:</>',
        );
        $this->testarDownloadImagem(
          $detalhe['image']['download'],
          $mapeado['imagem_path'],
        );
      }

      $this->newLine();
    }

    // Resumo
    $this->line('<options=bold>[ 4 ] Resumo do diagnóstico</>');
    $this->newLine();
    $this->line("  Total de notícias no Plone : <fg=yellow>{$total}</>");
    $this->line(
      '  Notícias analisadas        : <fg=yellow>' . count($itens) . '</>',
    );
    $this->newLine();
    $this->line(
      '  <fg=green;options=bold>✔ API funcionando! Pronto para importação.</>',
    );
    $this->line(
      '  <fg=yellow>⚠  Lembre-se de avisar o time Plone antes de importar tudo.</>',
    );
    $this->line(
      '     Eles criarão um processo dedicado para não derrubar o portal.',
    );
    $this->newLine();

    // Logout
    $this->logout();

    return self::SUCCESS;
  }

  // Autenticação

  private function autenticar(string $login, string $password): bool
  {
    try {
      $response = Http::withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ])->post("{$this->baseUrl}/@login", [
        'login' => $login,
        'password' => $password,
      ]);

      if (!$response->successful()) {
        $this->error(
          "  ✘ Falha na autenticação. Status: {$response->status()}",
        );
        $this->line('  Resposta: ' . $response->body());
        return false;
      }

      $this->token = $response->json('token');

      if (!$this->token) {
        $this->error('  ✘ Token não encontrado na resposta.');
        $this->line('  Resposta: ' . $response->body());
        return false;
      }

      return true;
    } catch (\Exception $e) {
      $this->error('  ✘ Erro ao conectar: ' . $e->getMessage());
      return false;
    }
  }

  private function logout(): void
  {
    try {
      Http::withToken($this->token)
        ->withHeaders(['Accept' => 'application/json'])
        ->post("{$this->baseUrl}/@logout");
    } catch (\Exception) {
      // silencioso
    }
  }

  // Busca de notícias

  private function buscarNoticias(string $path, int $quantidade): ?array
  {
    $estrategias = [
      // Tipo real encontrado no diagnóstico do portal da Polícia Civil PB
      '@search portal_type=collective.nitf.content + path' => fn() => Http::withToken(
        $this->token,
      )
        ->withHeaders(['Accept' => 'application/json'])
        ->get("{$this->baseUrl}/@search", [
          'portal_type' => 'collective.nitf.content',
          'path' => "/{$path}",
          'b_size' => $quantidade,
          'b_start' => 0,
          'sort_on' => 'effective',
          'sort_order' => 'descending',
        ]),

      // collective.nitf.content sem path
      '@search portal_type=collective.nitf.content' => fn() => Http::withToken(
        $this->token,
      )
        ->withHeaders(['Accept' => 'application/json'])
        ->get("{$this->baseUrl}/@search", [
          'portal_type' => 'collective.nitf.content',
          'b_size' => $quantidade,
          'b_start' => 0,
          'sort_on' => 'effective',
          'sort_order' => 'descending',
        ]),

      // Pasta diretamente (sem filtro portal_type)
      "/{$path} como pasta" => fn() => Http::withToken($this->token)
        ->withHeaders(['Accept' => 'application/json'])
        ->get("{$this->baseUrl}/{$path}", [
          'b_size' => $quantidade,
          'b_start' => 0,
        ]),

      // @search portal_type=News Item + path (fallback outros portais)
      '@search portal_type=News Item + path' => fn() => Http::withToken(
        $this->token,
      )
        ->withHeaders(['Accept' => 'application/json'])
        ->get("{$this->baseUrl}/@search", [
          'portal_type' => 'News Item',
          'path' => "/{$path}",
          'b_size' => $quantidade,
          'b_start' => 0,
          'sort_on' => 'effective',
          'sort_order' => 'descending',
        ]),

      // @search portal_type=News Item (sem path)
      '@search portal_type=News Item' => fn() => Http::withToken($this->token)
        ->withHeaders(['Accept' => 'application/json'])
        ->get("{$this->baseUrl}/@search", [
          'portal_type' => 'News Item',
          'b_size' => $quantidade,
          'b_start' => 0,
          'sort_on' => 'effective',
          'sort_order' => 'descending',
        ]),

      // @search sem filtro (pode misturar imagens e outros tipos)
      '@search sem filtro' => fn() => Http::withToken($this->token)
        ->withHeaders(['Accept' => 'application/json'])
        ->get("{$this->baseUrl}/@search", [
          'b_size' => $quantidade,
          'b_start' => 0,
        ]),
    ];

    foreach ($estrategias as $nome => $chamada) {
      try {
        $response = $chamada();
        $dados = $response->json();
        $total = $dados['items_total'] ?? null;
        $itens = $dados['items'] ?? [];

        if ($response->successful() && $total !== null) {
          $this->line(
            "  <fg=green>✔ Busca funcionou:</> <fg=yellow>{$nome}</>",
          );

          if ($total === 0) {
            // Mostra tipos disponíveis
            $this->warn("  ⚠ Total = 0 com «{$nome}»");
            continue; // tenta próxima
          }

          // Mostra tipos encontrados
          $tipos = collect($itens)->pluck('@type')->unique()->values()->all();
          $this->line(
            '  Tipos encontrados: <fg=gray>' . implode(', ', $tipos) . '</>',
          );

          return $dados;
        }

        $this->warn("  ⚠ «{$nome}» retornou {$response->status()}");
      } catch (\Exception $e) {
        $this->warn("  ⚠ «{$nome}» erro: {$e->getMessage()}");
      }
    }

    $this->error('  ✘ Todas as estratégias retornaram 0 resultados.');
    $this->diagnosticarTiposDisponiveis();

    return ['items_total' => 0, 'items' => []];
  }

  private function diagnosticarTiposDisponiveis(): void
  {
    $this->newLine();
    $this->line(
      '  <fg=cyan> Verificando tipos de conteúdo disponíveis no portal...</>',
    );

    try {
      $response = Http::withToken($this->token)
        ->withHeaders(['Accept' => 'application/json'])
        ->get("{$this->baseUrl}/@search", ['b_size' => 20, 'b_start' => 0]);

      if ($response->successful()) {
        $itens = $response->json()['items'] ?? [];
        $tipos = collect($itens)->pluck('@type')->countBy()->sortDesc()->all();

        if (empty($tipos)) {
          $this->warn('  Nenhum item encontrado no portal.');
        } else {
          $this->line('  Tipos no portal (top 20 itens):');
          foreach ($tipos as $tipo => $count) {
            $this->line("    <fg=yellow>{$tipo}</>: {$count} item(s)");
          }
          $this->newLine();
          $this->line(
            '  <fg=cyan> Dica:</> Rode novamente informando o tipo correto, ex:',
          );
          $this->line(
            '     php artisan noticias:diagnosticar-plone --path=<caminho_correto>',
          );
        }
      }
    } catch (\Exception) {
      $this->warn('  Não foi possível listar tipos disponíveis.');
    }
  }

  private function buscarDetalhe(string $url): ?array
  {
    try {
      $response = Http::withToken($this->token)
        ->withHeaders(['Accept' => 'application/json'])
        ->get($url);

      return $response->successful() ? $response->json() : null;
    } catch (\Exception) {
      return null;
    }
  }

  // Mapeamento

  private function mapearCampos(array $item): array
  {
    $titulo = $item['title'] ?? '';
    $slug = $this->sanitizeFolderName($titulo);
    $ext = 'jpg';

    if (isset($item['image']['content-type'])) {
      $mime = $item['image']['content-type'];
      $ext = match (true) {
        str_contains($mime, 'png') => 'png',
        str_contains($mime, 'gif') => 'gif',
        str_contains($mime, 'webp') => 'webp',
        default => 'jpg',
      };
    }

    $imagemPath = "noticias/{$slug}/capa/capa.{$ext}";
    $status =
      ($item['review_state'] ?? '') === 'published' ? 'publicado' : 'rascunho';
    $dataPub = isset($item['effective'])
      ? Carbon::parse($item['effective'])->format('Y-m-d')
      : now()->format('Y-m-d');

    return [
      'titulo' => $titulo,
      'descricao_curta' => $item['description'] ?? '',
      'conteudo' => $item['text']['data'] ?? '',
      'imagem' => isset($item['image']) ? $imagemPath : null,
      'imagem_path' => $imagemPath,
      'data_publicacao' => $dataPub,
      'status' => $status,
      'destaque' => false,
      'visualizacoes' => 0,
    ];
  }

  // Teste de download da imagem

  private function testarDownloadImagem(
    string $downloadUrl,
    string $imagemPath,
  ): void {
    try {
      $response = Http::withToken($this->token)
        ->timeout(10)
        ->head($downloadUrl);

      if ($response->successful()) {
        $tamanho = $response->header('Content-Length');
        $tamanhoFmt = $tamanho
          ? number_format($tamanho / 1024, 1) . ' KB'
          : 'tamanho desconhecido';
        $this->line("  <fg=green>✔ Imagem acessível</> ({$tamanhoFmt})");
        $this->line(
          "     Seria salva em: <fg=gray>storage/public/{$imagemPath}</>",
        );
      } else {
        $this->line(
          "  <fg=yellow>⚠ HEAD retornou {$response->status()} — tentando GET...</>",
        );

        // Tentar GET com range para não baixar tudo
        $response = Http::withToken($this->token)
          ->withHeaders(['Range' => 'bytes=0-1023'])
          ->timeout(10)
          ->get($downloadUrl);

        if ($response->successful() || $response->status() === 206) {
          $this->line('  <fg=green>✔ Imagem acessível via GET</>');
          $this->line(
            "     Seria salva em: <fg=gray>storage/public/{$imagemPath}</>",
          );
        } else {
          $this->line(
            "  <fg=red>✘ Imagem inacessível (status {$response->status()})</>",
          );
        }
      }
    } catch (\Exception $e) {
      $this->line(
        "  <fg=red>✘ Erro ao verificar imagem: {$e->getMessage()}</>",
      );
    }
  }

  // Helpers

  private function truncar(string $texto, int $max = 60): string
  {
    $texto = trim(preg_replace('/\s+/', ' ', $texto));
    return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max) . '…' : $texto;
  }

  /**
   * Replica a lógica do UploadHelper::sanitizeFolderName()
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
