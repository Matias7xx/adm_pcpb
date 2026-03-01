<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Traits\Auditable;

class Veiculo extends Model
{
  use HasFactory, SoftDeletes, Auditable;

  protected string $auditModulo = 'veiculo';
  protected string $auditLabel = 'titulo';
  protected array $auditExclude = ['downloads'];

  protected $fillable = [
    'titulo',
    'descricao',
    'arquivo',
    'tipo_arquivo',
    'tamanho_kb',
    'data_publicacao',
    'dias_exibicao',
    'data_expiracao',
    'ativo',
    'downloads',
    'ordem',
  ];

  protected $casts = [
    'ativo' => 'boolean',
    'downloads' => 'integer',
    'ordem' => 'integer',
    'dias_exibicao' => 'integer',
    'tamanho_kb' => 'integer',
    'data_publicacao' => 'date',
    'data_expiracao' => 'date',
  ];

  protected $appends = [
    'url_download',
    'url_preview',
    'expirado',
    'dias_restantes',
    'tamanho_formatado',
    'status_display',
  ];

  /**
   * Boot do model para calcular data de expiração automaticamente
   */
  protected static function boot()
  {
    parent::boot();

    static::creating(function ($veiculo) {
      if (!$veiculo->data_publicacao) {
        $veiculo->data_publicacao = now();
      }

      if (!$veiculo->data_expiracao) {
        $veiculo->data_expiracao = Carbon::parse(
          $veiculo->data_publicacao,
        )->addDays($veiculo->dias_exibicao);
      }
    });

    static::updating(function ($veiculo) {
      // Se mudou data_publicacao ou dias_exibicao, recalcula expiração
      if ($veiculo->isDirty(['data_publicacao', 'dias_exibicao'])) {
        $veiculo->data_expiracao = Carbon::parse(
          $veiculo->data_publicacao,
        )->addDays($veiculo->dias_exibicao);
      }
    });
  }

  /**
   * Obter URL de download do documento
   */
  public function getUrlDownloadAttribute()
  {
    if (!$this->arquivo) {
      return null;
    }

    return route('veiculos.download', $this->id);
  }

  /**
   * Obter URL de preview do documento
   */
  public function getUrlPreviewAttribute()
  {
    if (!$this->arquivo) {
      return null;
    }

    return route('veiculos.preview', $this->id);
  }

  /**
   * Verificar se o documento está expirado
   */
  public function getExpiradoAttribute()
  {
    return Carbon::parse($this->data_expiracao)->isPast();
  }

  /**
   * Obter dias restantes até expiração
   */
  public function getDiasRestantesAttribute()
  {
    if ($this->expirado) {
      return 0;
    }

    // Usa ceil() para arredondar para cima (se falta 0.5 dias = 1 dia)
    $diasRestantes = Carbon::now()->diffInDays($this->data_expiracao, false);

    return (int) ceil($diasRestantes);
  }

  /**
   * Obter status de exibição formatado
   */
  public function getStatusDisplayAttribute()
  {
    // Se inativo
    if (!$this->ativo) {
      return [
        'text' => 'Inativo',
        'type' => 'inativo',
        'color' => 'slate',
        'icon' => 'pause',
      ];
    }

    // Se expirado - LISTADO PARA LEILÃO
    if ($this->expirado) {
      return [
        'text' => 'Prazo Encerrado',
        'type' => 'leilao',
        'color' => 'blue',
        'icon' => 'gavel',
      ];
    }

    // Se crítico (3 dias ou menos)
    if ($this->dias_restantes <= 3) {
      return [
        'text' =>
          $this->dias_restantes === 1
            ? '1 dia restante - Prazo Final'
            : "{$this->dias_restantes} dias restantes - Prazo Final",
        'type' => 'critico',
        'color' => 'red',
        'icon' => 'alert',
      ];
    }

    // Se próximo (7 dias ou menos)
    if ($this->dias_restantes <= 7) {
      return [
        'text' => "{$this->dias_restantes} dias restantes - Prazo Final",
        'type' => 'proximo',
        'color' => 'yellow',
        'icon' => 'clock',
      ];
    }

    // Disponível
    return [
      'text' => 'Em Prazo Legal',
      'type' => 'disponivel',
      'color' => 'green',
      'icon' => 'check',
    ];
  }

  /**
   * Obter tamanho formatado do arquivo
   */
  public function getTamanhoFormatadoAttribute()
  {
    if (!$this->tamanho_kb) {
      return 'N/A';
    }

    if ($this->tamanho_kb < 1024) {
      return $this->tamanho_kb . ' KB';
    }

    return round($this->tamanho_kb / 1024, 2) . ' MB';
  }

  /**
   * Obter ícone baseado no tipo de arquivo
   */
  public function getIconeAttribute()
  {
    return match ($this->tipo_arquivo) {
      'pdf' => '📄',
      'excel' => '📊',
      default => '📁',
    };
  }

  /**
   * Incrementar contador de downloads
   */
  public function incrementarDownloads()
  {
    static::withoutEvents(function () {
      $this->increment('downloads');
    });
  }

  /**
   * Scope para documentos ativos
   */
  public function scopeAtivos($query)
  {
    return $query->where('ativo', true);
  }

  /**
   * Scope para documentos não expirados
   */
  public function scopeNaoExpirados($query)
  {
    return $query->where('data_expiracao', '>=', now());
  }

  /**
   * Scope para documentos válidos (ativos e não expirados)
   */
  public function scopeValidos($query)
  {
    return $query->ativos()->naoExpirados();
  }

  /**
   * Scope para ordenar por ordem definida
   */
  public function scopeOrdenados($query)
  {
    return $query->orderBy('ordem', 'asc')->orderBy('id', 'desc');
  }

  /**
   * Obter documentos para exibição pública
   */
  public static function paraPublico($limite = null)
  {
    $query = self::validos()->ordenados();

    if ($limite) {
      $query->limit($limite);
    }

    return $query->get();
  }

  /**
   * Obter caminho completo do arquivo no storage
   */
  public function getCaminhoArquivoAttribute()
  {
    return 'veiculos/' . $this->arquivo;
  }

  /**
   * Deletar arquivo do storage ao deletar o registro
   */
  protected static function booted()
  {
    static::deleting(function ($veiculo) {
      if (
        $veiculo->arquivo &&
        Storage::disk('s3')->exists($veiculo->caminho_arquivo)
      ) {
        Storage::disk('s3')->delete($veiculo->caminho_arquivo);
      }
    });
  }
}
