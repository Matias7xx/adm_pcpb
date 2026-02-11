<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
  /**
   * Retornar banners ativos para exibição pública
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index()
  {
    $banners = Banner::ativos()
      ->ordenados()
      ->get()
      ->filter(function ($banner) {
        return $banner->podeSerExibido();
      })
      ->map(function ($banner) {
        return [
          'id' => $banner->id,
          'titulo' => $banner->titulo,
          'descricao' => $banner->descricao,
          'imagem' => $banner->imagem_url,
          'link' => $banner->link,
          'nova_aba' => $banner->nova_aba,
        ];
      })
      ->values();

    return response()->json($banners);
  }
}
