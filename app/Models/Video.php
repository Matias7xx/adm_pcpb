<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Video extends Model
{
  use HasFactory, Auditable;

  protected string $auditModulo = 'video';
  protected string $auditLabel = 'titulo';
  protected array $auditExclude = ['visualizacoes'];

  protected $fillable = [
    'titulo',
    'youtube_url',
    'youtube_id',
    'descricao',
    'ordem',
    'ativo',
    'visualizacoes',
  ];

  protected $casts = [
    'ativo' => 'boolean',
    'visualizacoes' => 'integer',
    'ordem' => 'integer',
  ];

  protected $appends = ['thumbnail_url', 'embed_url', 'watch_url'];

  /**
   * Boot do model para extrair ID do YouTube automaticamente
   */
  protected static function boot()
  {
    parent::boot();

    static::saving(function ($video) {
      if ($video->youtube_url && !$video->youtube_id) {
        $video->youtube_id = self::extrairYoutubeId($video->youtube_url);
      }
    });
  }

  /**
   * Extrair ID do vídeo do YouTube a partir da URL
   */
  public static function extrairYoutubeId($url)
  {
    // Padrões de URL do YouTube
    $patterns = [
      '/youtube\.com\/watch\?v=([^\&\?\/]+)/',
      '/youtube\.com\/embed\/([^\&\?\/]+)/',
      '/youtu\.be\/([^\&\?\/]+)/',
      '/youtube\.com\/v\/([^\&\?\/]+)/',
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
      }
    }

    return null;
  }

  /**
   * Obter URL da thumbnail do YouTube
   */
  public function getThumbnailUrlAttribute()
  {
    if (!$this->youtube_id) {
      return null;
    }

    // Thumbnail em alta qualidade
    return "https://img.youtube.com/vi/{$this->youtube_id}/maxresdefault.jpg";
  }

  /**
   * Obter URL da thumbnail em qualidade média
   */
  public function getThumbnailMediumAttribute()
  {
    if (!$this->youtube_id) {
      return null;
    }

    return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
  }

  /**
   * Obter URL embed do YouTube
   */
  public function getEmbedUrlAttribute()
  {
    if (!$this->youtube_id) {
      return null;
    }

    return "https://www.youtube.com/embed/{$this->youtube_id}";
  }

  /**
   * Obter URL do vídeo no YouTube
   */
  public function getWatchUrlAttribute()
  {
    if (!$this->youtube_id) {
      return $this->youtube_url;
    }

    return "https://www.youtube.com/watch?v={$this->youtube_id}";
  }

  /**
   * Incrementar visualizações
   */
  public function incrementarVisualizacoes()
  {
    $this->increment('visualizacoes');
  }

  /**
   * Scope para vídeos ativos
   */
  public function scopeAtivos($query)
  {
    return $query->where('ativo', true);
  }

  /**
   * Scope para ordenar por ordem definida
   */
  public function scopeOrdenados($query)
  {
    return $query->orderBy('ordem', 'asc')->orderBy('created_at', 'desc');
  }

  /**
   * Obter vídeos para exibição na home
   */
  public static function paraHome($limite = null)
  {
    $query = self::ativos()->ordenados();

    if ($limite) {
      $query->limit($limite);
    }

    return $query->get();
  }
}
