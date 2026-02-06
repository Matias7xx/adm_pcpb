<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operacao extends Model
{
    use HasFactory;

    protected $table = 'operacoes';

    protected $fillable = [
        'user_id',
        'policial_responsavel_nome',
        'policial_responsavel_matricula',
        'unidade_policial_responsavel',
        'nome_operacao',
        'autoridade_responsavel_nome',
        'autoridade_responsavel_matricula',
        'origem_operacao',
        'uf_responsavel',
        'data_operacao',
        'local_briefing',
        'horario_briefing',
        'quantidade_total_alvos',
        'quantidade_mandados_prisao',
        'quantidade_mandados_busca_apreensao',
        'quantidade_mandados_busca_apreensao_infrator',
        'quantidade_alvos_outros_estados',
        'quantidade_policiais_empregados',
        'quantidade_viaturas_empregadas',
        'cidades_alvo',
        'crimes_investigados',
        'vinculada_unidade',
        'vinculada_unidade_especializada',
        'outra_unidade_policial',
        'vinculada_delegacia_seccional',
        'solicitacao_apoio_diop',
    ];

    protected $casts = [
        'data_operacao' => 'date',
        'quantidade_total_alvos' => 'integer',
        'quantidade_mandados_prisao' => 'integer',
        'quantidade_mandados_busca_apreensao' => 'integer',
        'quantidade_mandados_busca_apreensao_infrator' => 'integer',
        'quantidade_alvos_outros_estados' => 'integer',
        'quantidade_policiais_empregados' => 'integer',
        'quantidade_viaturas_empregadas' => 'integer',
    ];

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
        return $query->whereBetween('data_operacao', [$dataInicio, $dataFim]);
    }

    public function scopePorOrigem($query, $origem)
    {
        return $query->where('origem_operacao', $origem);
    }

    public function scopePorUF($query, $uf)
    {
        return $query->where('uf_responsavel', $uf);
    }

    /**
     * Accessor para formatar data
     */
    public function getDataOperacaoFormatadaAttribute()
    {
        return $this->data_operacao->format('d/m/Y');
    }

    /**
     * Accessor para formatar horário
     */
    public function getHorarioBriefingFormatadoAttribute()
    {
        if (!$this->horario_briefing) {
            return '';
        }
        
        // Se já está no formato HH:MM, retorna direto
        if (preg_match('/^\d{2}:\d{2}$/', $this->horario_briefing)) {
            return $this->horario_briefing;
        }
        
        // Se for datetime completo, extrai só a hora
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $this->horario_briefing)) {
            return substr($this->horario_briefing, 11, 5);
        }
        
        return $this->horario_briefing;
    }

    /**
     * Calcula total de mandados
     */
    public function getTotalMandadosAttribute()
    {
        return $this->quantidade_mandados_prisao + 
               $this->quantidade_mandados_busca_apreensao + 
               $this->quantidade_mandados_busca_apreensao_infrator;
    }

    /**
     * Retorna array com estatísticas da operação
     */
    public function getEstatisticas()
    {
        return [
            'total_alvos' => $this->quantidade_total_alvos,
            'total_mandados' => $this->total_mandados,
            'mandados_prisao' => $this->quantidade_mandados_prisao,
            'mandados_busca' => $this->quantidade_mandados_busca_apreensao,
            'mandados_busca_infrator' => $this->quantidade_mandados_busca_apreensao_infrator,
            'alvos_outros_estados' => $this->quantidade_alvos_outros_estados,
            'policiais_empregados' => $this->quantidade_policiais_empregados,
            'viaturas_empregadas' => $this->quantidade_viaturas_empregadas,
        ];
    }

    /**
     * Arrays de opções para selects
     */
    public static function getOrigensOperacao()
    {
        return [
            'Nacional' => 'Nacional (Ministério da Justiça e Segurança Pública)',
            'Estadual' => 'Estadual (Investigação da PCPB)',
            'Apoio a outro Estado' => 'Apoio a outro Estado',
        ];
    }

    public static function getUnidadesFederativas()
    {
        return [
            'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
            'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
            'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
            'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
            'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
        ];
    }

    public static function getUnidadesVinculadas()
    {
        return [
            'Delegacia Geral',
            '1ª SRPC',
            '2ª SRPC',
            '3ª SRPC',
            '4ª SRPC',
            'COORDEAM',
            'DIOP',
        ];
    }

    public static function getUnidadesEspecializadas()
    {
        return [
            'DAV' => 'DAV (Acidente de Veículos)',
            'DCCPAT' => 'DCCPAT (Patrimônio)',
            'DCCPES' => 'DCCPES (Homicídios)',
            'DDF' => 'DDF (Defraudações)',
            'DEAM' => 'DEAM',
            'DEATI' => 'DEATI (Especializada de Atendimento ao Idoso)',
            'DEATUR' => 'DEATUR (Atendimento ao Turista)',
            'DECC' => 'DECC (Crimes Cibernéticos)',
            'DECCOR' => 'DECCOR (Combate a Corrupção)',
            'DECCOT' => 'DECCOT (Ordem Tributária)',
            'DECHRADI' => 'DECHRADI (Homofóbicos, Racismo e Delitos de Intolerância Religiosa)',
            'DECON' => 'DECON (Consumidor)',
            'DESARME' => 'DESARME',
            'DHE' => 'DHE (Homicídios e Entorpecentes)',
            'DMA' => 'DMA (Meio Ambiente)',
            'DRACO' => 'DRACO',
            'DRE' => 'DRE (Entorpecentes)',
            'DRFVC' => 'DRFVC',
            'GOE' => 'GOE',
            'GTE' => 'GTE (Grupo Tático Especial)',
            'DIJ' => 'DIJ (Infância e Juventude)',
            'DRCCIJ' => 'DRCCIJ (Repressão Contra Crimes a Infância e Juventude)',
            'DRF' => 'DRF (Roubos e Furtos)',
            'NH' => 'NH (Núcleo de Homicídio)',
            'NRQ' => 'NRQ (Núcleo de Repressão Qualificada)',
            'OUTRA' => 'OUTRA',
        ];
    }

    public static function getDelegaciasSeccionais()
    {
        $delegacias = [];
        for ($i = 1; $i <= 24; $i++) {
            $delegacias[] = "{$i}ª DSPC";
        }
        $delegacias[] = 'PREJUDICADO';
        return $delegacias;
    }

        /**
     * Relacionamento com resultado da operação (1:1)
     */
    public function resultado()
    {
        return $this->hasOne(ResultadoOperacao::class);
    }
}