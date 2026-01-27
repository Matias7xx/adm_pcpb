<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VideoPublicController extends Controller
{
  /**
   * Retornar vídeos ativos para exibição pública
   */
  public function index()
  {
    try {
      // Cache por 1 hora
      $videos = Cache::remember('videos_home', 3600, function () {
        return Video::ativos()
          ->ordenados()
          ->get()
          ->map(function ($video) {
            return [
              'id' => $video->id,
              'titulo' => $video->titulo,
              'descricao' => $video->descricao,
              'youtube_id' => $video->youtube_id,
              'youtube_url' => $video->youtube_url,
              'embed_url' => $video->embed_url,
              'thumbnail_url' => $video->thumbnail_url,
              'watch_url' => $video->watch_url,
            ];
          });
      });

      return response()->json($videos);
    } catch (\Exception $e) {
      \Log::error('Erro ao buscar vídeos: ' . $e->getMessage());
      return response()->json([], 200); // Retorna array vazio em caso de erro
    }
  }
}
