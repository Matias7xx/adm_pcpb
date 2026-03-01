<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class ResultadoOperacao extends Model
{
  use HasFactory, Auditable;

  protected string $auditModulo = 'resultado_operacao';
  protected string $auditLabel = 'operacao_id';

  protected $table = 'resultados_operacao';

  protected $fillable = [
    'operacao_id',
    'user_id',
    'autoridade_responsavel_nome',
    'autoridade_responsavel_matricula',
    'policial_responsavel_nome',
    'policial_responsavel_matricula',
    'unidade_policial_responsavel',
    'numero_processo_pje',
    'mandados_prisao_cumpridos',
    'mandados_prisao_cumpridos_detalhes',
    'mandados_prisao_nao_cumpridos',
    'mandados_busca_cumpridos',
    'mandados_busca_infrator_cumpridos',
    'mandados_busca_infrator_nao_cumpridos',
    'prisoes_flagrante',
    'quantidade_armas_apreendidas',
    'tipo_arma_apreendida',
    'detalhes_armas_apreendidas',
    'municoes_apreendidas',
    'entorpecente_apreendido',
    'detalhes_entorpecentes',
    'valores_dinheiro',
    'veiculos_apreendidos',
    'demais_objetos_apreendidos',
    'outras_informacoes',
  ];

  protected $casts = [
    'mandados_prisao_cumpridos' => 'integer',
    'mandados_prisao_nao_cumpridos' => 'integer',
    'mandados_busca_cumpridos' => 'integer',
    'mandados_busca_infrator_cumpridos' => 'integer',
    'mandados_busca_infrator_nao_cumpridos' => 'integer',
    'prisoes_flagrante' => 'integer',
    'quantidade_armas_apreendidas' => 'integer',
    'valores_dinheiro' => 'decimal:2',
    'tipo_arma_apreendida' => 'array',
    'entorpecente_apreendido' => 'array',
  ];

  /**
   * Relacionamento com a operação original
   */
  public function operacao(): BelongsTo
  {
    return $this->belongsTo(Operacao::class);
  }

  /**
   * Relacionamento com usuário
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Scopes para facilitar queries no Metabase
   */
  public function scopePorUnidade($query, $unidade)
  {
    return $query->where('unidade_policial_responsavel', $unidade);
  }

  public function scopePorPeriodo($query, $dataInicio, $dataFim)
  {
    return $query->whereBetween('created_at', [$dataInicio, $dataFim]);
  }

  public function scopeComEntorpecentes($query)
  {
    return $query->whereJsonContains('entorpecente_apreendido', 'NÃO', 'not');
  }

  public function scopeComArmas($query)
  {
    return $query->where('quantidade_armas_apreendidas', '>', 0);
  }

  /**
   * Calcula total de mandados cumpridos
   */
  public function getTotalMandadosCumpridosAttribute()
  {
    return $this->mandados_prisao_cumpridos +
      $this->mandados_busca_cumpridos +
      $this->mandados_busca_infrator_cumpridos;
  }

  /**
   * Calcula total de mandados não cumpridos
   */
  public function getTotalMandadosNaoCumpridosAttribute()
  {
    return $this->mandados_prisao_nao_cumpridos +
      $this->mandados_busca_infrator_nao_cumpridos;
  }

  /**
   * Calcula taxa de êxito nos mandados
   */
  public function getTaxaExitoAttribute()
  {
    $total =
      $this->total_mandados_cumpridos + $this->total_mandados_nao_cumpridos;

    if ($total === 0) {
      return 0;
    }

    return round(($this->total_mandados_cumpridos / $total) * 100, 2);
  }

  /**
   * Formata valor em dinheiro
   */
  public function getValoresDinheiroFormatadoAttribute()
  {
    return 'R$ ' . number_format($this->valores_dinheiro, 2, ',', '.');
  }

  /**
   * Verifica se tem entorpecentes
   */
  public function getTemEntorpecentesAttribute()
  {
    $entorpecentes = $this->entorpecente_apreendido ?? [];
    return !empty($entorpecentes) && !in_array('NÃO', $entorpecentes);
  }

  /**
   * Retorna tipos de armas formatados para exibição
   */
  public function getTiposArmaFormatadosAttribute()
  {
    $tipos = $this->tipo_arma_apreendida ?? [];
    if (empty($tipos)) {
      return 'Nenhum tipo informado';
    }
    return implode(', ', $tipos);
  }

  /**
   * Retorna tipos de entorpecentes formatados para exibição
   */
  public function getEntorpecentesFormatadosAttribute()
  {
    $tipos = $this->entorpecente_apreendido ?? [];
    if (empty($tipos)) {
      return 'Não informado';
    }
    return implode(', ', $tipos);
  }

  /**
   * Retorna array com estatísticas do resultado
   */
  public function getEstatisticas()
  {
    return [
      'total_mandados_cumpridos' => $this->total_mandados_cumpridos,
      'total_mandados_nao_cumpridos' => $this->total_mandados_nao_cumpridos,
      'taxa_exito' => $this->taxa_exito,
      'mandados_prisao_cumpridos' => $this->mandados_prisao_cumpridos,
      'mandados_prisao_nao_cumpridos' => $this->mandados_prisao_nao_cumpridos,
      'mandados_busca_cumpridos' => $this->mandados_busca_cumpridos,
      'mandados_busca_infrator_cumpridos' =>
        $this->mandados_busca_infrator_cumpridos,
      'mandados_busca_infrator_nao_cumpridos' =>
        $this->mandados_busca_infrator_nao_cumpridos,
      'prisoes_flagrante' => $this->prisoes_flagrante,
      'total_prisoes' =>
        $this->mandados_prisao_cumpridos + $this->prisoes_flagrante,
      'quantidade_armas' => $this->quantidade_armas_apreendidas,
      'tem_entorpecentes' => $this->tem_entorpecentes,
      'valores_dinheiro' => $this->valores_dinheiro,
    ];
  }

  /**
   * Arrays de opções para checkboxes
   */
  public static function getTiposArma()
  {
    return [
      'NENHUMA' => 'Nenhuma arma apreendida',
      'REVÓLVER' => 'Revólver',
      'PISTOLA' => 'Pistola',
      'ESPINGARDA' => 'Espingarda',
      'FUZIL' => 'Fuzil',
      'ARMA ARTESANAL' => 'Arma Artesanal',
      'EXPLOSIVO' => 'Explosivo',
      'PREJUDICADO' => 'Prejudicado',
    ];
  }

  public static function getTiposEntorpecente()
  {
    return [
      'NENHUM' => 'Nenhum entorpecente apreendido',
      'MACONHA' => 'Maconha',
      'COCAINA' => 'Cocaína',
      'CRACK' => 'Crack',
      'SKANK' => 'Skank',
      'HEROINA' => 'Heroína',
      'LSD' => 'LSD',
      'ECSTASY (MDMA)' => 'Ecstasy (MDMA)',
      'OUTROS' => 'Outros',
    ];
  }
}
