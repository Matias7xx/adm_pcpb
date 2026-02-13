<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VeiculoPublicoController extends Controller
{
  /**
   * Retornar documentos válidos E expirados para exibição pública
   */
  public function index()
  {
    try {
      // Cache por 30 minutos
      $veiculos = Cache::remember('veiculos_publicos', 1800, function () {
        return Veiculo::ativos() // Removido naoExpirados()
          ->ordenados()
          ->get()
          ->map(function ($veiculo) {
            return [
              'id' => $veiculo->id,
              'titulo' => $veiculo->titulo,
              'descricao' => $veiculo->descricao,
              'tipo_arquivo' => $veiculo->tipo_arquivo,
              'icone' => $veiculo->icone,
              'tamanho_formatado' => $veiculo->tamanho_formatado,
              'data_publicacao' => $veiculo->data_publicacao->format('d/m/Y'),
              'data_expiracao' => $veiculo->data_expiracao->format('d/m/Y'),
              'dias_restantes' => $veiculo->dias_restantes,
              'downloads' => $veiculo->downloads,
              'url_download' => $veiculo->url_download,
              'url_preview' => $veiculo->url_preview,
              'status_display' => $veiculo->status_display,
              'expirado' => $veiculo->expirado,
            ];
          });
      });

      return response()->json($veiculos);
    } catch (\Exception $e) {
      Log::error('Erro ao buscar veículos: ' . $e->getMessage());
      return response()->json([], 200);
    }
  }

  /**
   * Preview do documento (visualizar inline)
   */
  public function preview(Veiculo $veiculo)
  {
    try {
      if (!$veiculo->ativo) {
        abort(404, 'Documento não disponível.');
      }

      $caminhoArquivo = 'veiculos/' . $veiculo->arquivo;

      // Verificar se arquivo existe
      if (!Storage::disk('s3')->exists($caminhoArquivo)) {
        Log::error('Arquivo não encontrado no S3', [
          'veiculo_id' => $veiculo->id,
          'caminho' => $caminhoArquivo,
        ]);
        abort(404, 'Arquivo não encontrado.');
      }

      // Obter conteúdo do arquivo
      $conteudo = Storage::disk('s3')->get($caminhoArquivo);
      $mimeType = Storage::disk('s3')->mimeType($caminhoArquivo);

      // Retornar inline (para visualizar, não baixar)
      return response($conteudo)
        ->header('Content-Type', $mimeType)
        ->header(
          'Content-Disposition',
          'inline; filename="' . $veiculo->arquivo . '"',
        );
    } catch (\Exception $e) {
      Log::error('Erro ao fazer preview do documento', [
        'veiculo_id' => $veiculo->id,
        'erro' => $e->getMessage(),
      ]);
      abort(500, 'Erro ao visualizar documento.');
    }
  }

  /**
   * Download do documento
   */
  public function download(Veiculo $veiculo)
  {
    try {
      if (!$veiculo->ativo) {
        abort(404, 'Documento não disponível.');
      }

      // Verificar se arquivo existe
      if (!Storage::disk('s3')->exists($veiculo->caminho_arquivo)) {
        Log::error('Arquivo não encontrado no S3', [
          'veiculo_id' => $veiculo->id,
          'caminho' => $veiculo->caminho_arquivo,
        ]);
        abort(404, 'Arquivo não encontrado.');
      }

      // Incrementar contador de downloads
      $veiculo->incrementarDownloads();

      // Obter conteúdo do arquivo
      $conteudo = Storage::disk('s3')->get($veiculo->caminho_arquivo);
      $mimeType = Storage::disk('s3')->mimeType($veiculo->caminho_arquivo);

      // Retornar arquivo para download
      return response($conteudo)
        ->header('Content-Type', $mimeType)
        ->header(
          'Content-Disposition',
          'attachment; filename="' . $veiculo->arquivo . '"',
        );
    } catch (\Exception $e) {
      Log::error('Erro ao fazer download do documento: ' . $e->getMessage());
      abort(500, 'Erro ao fazer download.');
    }
  }
}
