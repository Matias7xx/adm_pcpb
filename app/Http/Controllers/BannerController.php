<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
  /**
   * Retorna banners ativos para exibição pública
   */
  public function index(Request $request)
  {
    $tipo = $request->query('tipo', 'topo'); // default = topo

    $banners = Banner::ativos()
      ->tipo($tipo)
      ->ordenados()
      ->get()
      ->filter(fn($b) => $b->podeSerExibido())
      ->map(fn($b) => [
        'id'       => $b->id,
        'titulo'   => $b->titulo,
        'descricao'=> $b->descricao,
        'imagem'   => $b->imagem_url,
        'link'     => $b->link,
        'nova_aba' => $b->nova_aba,
      ])
      ->values();

    // Banners inferiores: retorna no máximo 4
    if ($tipo === 'inferior') {
      $banners = $banners->take(4)->values();
    }

    return response()->json($banners);
  }
}