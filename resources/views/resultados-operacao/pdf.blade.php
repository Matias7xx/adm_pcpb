<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Operação - {{ $operacao->nome_operacao }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }

        .brasao {
            width: 80px; 
            height: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #000;
            font-size: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header h2 {
            color: #000;
            font-size: 16px;
            font-weight: bold;
        }

        .secao {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .secao-titulo {
            background-color: #000;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .campo {
            margin-bottom: 12px;
        }

        .campo-label {
            font-weight: bold;
            color: #000;
            display: block;
            margin-bottom: 3px;
        }

        .campo-valor {
            color: #000;
            padding-left: 10px;
        }

        .grid-2col {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .grid-col {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
        }

        .tabela {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tabela th {
            background-color: #333;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            border: 1px solid #000;
        }

        .tabela td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11px;
            color: #000;
        }

        .tabela tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .destaque {
            background-color: #fff;
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
        }

        .destaque-sucesso {
            background-color: #d4edda;
            border: 1px solid #28a745;
            padding: 10px;
            margin: 15px 0;
        }

        .destaque-info {
            background-color: #d1ecf1;
            border: 1px solid #17a2b8;
            padding: 10px;
            margin: 15px 0;
        }

        .rodape {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 10px;
            color: #333;
        }

        .assinatura {
            margin-top: 50px;
            text-align: center;
        }

        .estatistica-box {
            text-align: center;
            padding: 15px;
            border: 2px solid #000;
            margin: 10px 0;
        }

        .estatistica-valor {
            font-size: 28px;
            font-weight: bold;
            color: #000;
        }

        .estatistica-label {
            font-size: 11px;
            color: #333;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/brasao.png') }}" class="brasao" alt="Brasão">
        
        <h1>RESULTADO DA OPERAÇÃO</h1>
        <h2>DEBRIEFING</h2>
        <p style="margin-top: 10px; color: #333;">
            Gerado em: {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="secao">
        <div class="secao-titulo">RESUMO ESTATÍSTICO</div>
        
        <div style="display: table; width: 100%;">
            <div style="display: table-row;">
                <div style="display: table-cell; width: 25%; padding: 10px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor">{{ $estatisticas['total_prisoes'] }}</div>
                        <div class="estatistica-label">PRISÕES TOTAIS</div>
                    </div>
                </div>
                <div style="display: table-cell; width: 25%; padding: 10px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor">{{ $estatisticas['quantidade_armas'] }}</div>
                        <div class="estatistica-label">ARMAS APREENDIDAS</div>
                    </div>
                </div>
                <div style="display: table-cell; width: 25%; padding: 10px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor">{{ $estatisticas['taxa_exito'] }}%</div>
                        <div class="estatistica-label">TAXA DE ÊXITO</div>
                    </div>
                </div>
                <div style="display: table-cell; width: 25%; padding: 10px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor" style="font-size: 18px;">R$ {{ number_format($estatisticas['valores_dinheiro'], 2, ',', '.') }}</div>
                        <div class="estatistica-label">VALORES APREENDIDOS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">IDENTIFICAÇÃO DA OPERAÇÃO</div>

        <div class="destaque">
            <div class="campo">
                <span class="campo-label">Nome da Operação:</span>
                <span class="campo-valor" style="font-size: 14px; font-weight: bold;">{{ $operacao->nome_operacao }}</span>
            </div>
        </div>

        <div class="grid-2col">
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Data da Deflagração:</span>
                    <span class="campo-valor">{{ $operacao->data_operacao_formatada }}</span>
                </div>
            </div>
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Unidade Responsável:</span>
                    <span class="campo-valor">{{ $resultado->unidade_policial_responsavel }}</span>
                </div>
            </div>
        </div>

        <div class="grid-2col">
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Origem da Operação:</span>
                    <span class="campo-valor">{{ $operacao->origem_operacao }}</span>
                </div>
            </div>
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">UF Responsável:</span>
                    <span class="campo-valor">{{ $operacao->uf_responsavel }}</span>
                </div>
            </div>
        </div>

        {{-- UFs do Alvo — só aparece quando origem = "Alvo em Outro Estado" --}}
        @if($operacao->origem_operacao === 'Alvo em outro Estado' && !empty($operacao->ufs_alvo_outros_estados))
        <div class="campo" style="margin-top: 6px; background: #dbeafe; padding: 8px 10px; border-radius: 4px; border-left: 4px solid #3b82f6;">
            <span class="campo-label" style="color: #1d4ed8;">Estado(s) e Quantidade de Alvos por Estado:</span>
            <span class="campo-valor" style="font-weight: bold; color: #1e40af;">
                @foreach($operacao->ufs_alvo_outros_estados as $uf => $qtd){{ $uf }} ({{ $qtd }} {{ $qtd == 1 ? 'alvo' : 'alvos' }}){{ !$loop->last ? ', ' : '' }}@endforeach
            </span>
        </div>
        @endif

        <div class="grid-2col" style="margin-top: 8px;">
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Vinculada à:</span>
                    <span class="campo-valor">{{ $operacao->vinculada_unidade }}</span>
                </div>
            </div>
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Unidade Executora:</span>
                    <span class="campo-valor">{{ $operacao->vinculada_unidade_especializada }}</span>
                </div>
            </div>
        </div>

        <div class="grid-2col">
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Delegacia Seccional:</span>
                    <span class="campo-valor">{{ $operacao->vinculada_delegacia_seccional }}</span>
                </div>
            </div>
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Número do Processo PJE:</span>
                    <span class="campo-valor">{{ $resultado->numero_processo_pje ?: '—' }}</span>
                </div>
            </div>
        </div>

        <div class="grid-2col">
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Local do Briefing:</span>
                    <span class="campo-valor">{{ $operacao->local_briefing }}</span>
                </div>
            </div>
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Horário do Briefing:</span>
                    <span class="campo-valor">{{ $operacao->horario_briefing_formatado ?: $operacao->horario_briefing }}</span>
                </div>
            </div>
        </div>

        <div class="campo">
            <span class="campo-label">Cidades Alvo:</span>
            <span class="campo-valor">{{ $operacao->cidades_alvo }}</span>
        </div>

        <div class="campo">
            <span class="campo-label">Crimes Investigados:</span>
            <span class="campo-valor">{{ $operacao->crimes_investigados }}</span>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">RESPONSÁVEIS</div>

            <div class="campo">
                <span class="campo-label">Autoridade Policial Responsável:</span>
                <span class="campo-valor">{{ $resultado->autoridade_responsavel_nome }}</span>
            </div>
            <div class="campo">
                <span class="campo-label">Matrícula:</span>
                <span class="campo-valor">{{ $resultado->autoridade_responsavel_matricula }}</span>
            </div>

        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ccc;">
            <div class="campo">
                <span class="campo-label">Policial Responsável pelo Preenchimento:</span>
                <span class="campo-valor">{{ $resultado->policial_responsavel_nome }}</span>
            </div>
            <div class="campo">
                <span class="campo-label">Matrícula:</span>
                <span class="campo-valor">{{ $resultado->policial_responsavel_matricula }}</span>
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">NÚMEROS DA OPERAÇÃO PLANEJADA</div>

        <div style="display: table; width: 100%;">
            <div style="display: table-row;">
                <div style="display: table-cell; width: 25%; padding: 5px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor">{{ $operacao->quantidade_total_alvos }}</div>
                        <div class="estatistica-label">TOTAL DE ALVOS</div>
                        @if($operacao->origem_operacao === 'Alvo em outro Estado' && !empty($operacao->ufs_alvo_outros_estados))
                        <div style="margin-top: 6px; border-top: 1px solid #ccc; padding-top: 4px;">
                            @foreach($operacao->ufs_alvo_outros_estados as $uf => $qtd)
                            <div style="display: flex; justify-content: space-between; font-size: 10px; color: #1e40af; font-weight: bold; padding: 1px 0;">
                                <span>{{ $uf }}</span><span>{{ $qtd }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <div style="display: table-cell; width: 25%; padding: 5px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor">{{ $operacao->quantidade_policiais_empregados }}</div>
                        <div class="estatistica-label">POLICIAIS EMPREGADOS</div>
                    </div>
                </div>
                <div style="display: table-cell; width: 25%; padding: 5px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor">{{ $operacao->quantidade_viaturas_empregadas }}</div>
                        <div class="estatistica-label">VIATURAS EMPREGADAS</div>
                    </div>
                </div>
                <div style="display: table-cell; width: 25%; padding: 5px;">
                    <div class="estatistica-box">
                        <div class="estatistica-valor">{{ $operacao->quantidade_alvos_outros_estados }}</div>
                        <div class="estatistica-label">ALVOS EM OUTROS ESTADOS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">MANDADOS E TAXA DE ÊXITO</div>
        
        <table class="tabela">
            <tr>
                <th>Tipo de Mandado</th>
                <th style="text-align: center; width: 90px;">Previstos</th>
                <th style="text-align: center; width: 90px;">Cumpridos</th>
                <th style="text-align: center; width: 90px;">Não Cumpridos</th>
            </tr>
            <tr>
                <td>Mandados de Prisão</td>
                <td style="text-align: center; color: #555;">{{ $operacao->quantidade_mandados_prisao }}</td>
                <td style="text-align: center; font-weight: bold; color: #28a745;">{{ $estatisticas['mandados_prisao_cumpridos'] }}</td>
                <td style="text-align: center; font-weight: bold; color: #dc3545;">{{ $estatisticas['mandados_prisao_nao_cumpridos'] }}</td>
            </tr>
            <tr>
                <td>Mandados de Busca e Apreensão</td>
                <td style="text-align: center; color: #555;">{{ $operacao->quantidade_mandados_busca_apreensao }}</td>
                <td style="text-align: center; font-weight: bold; color: #28a745;">{{ $estatisticas['mandados_busca_cumpridos'] }}</td>
                <td style="text-align: center;">—</td>
            </tr>
            <tr>
                <td>Mandados de Busca e Apreensão de Infrator</td>
                <td style="text-align: center; color: #555;">{{ $operacao->quantidade_mandados_busca_apreensao_infrator }}</td>
                <td style="text-align: center; font-weight: bold; color: #28a745;">{{ $estatisticas['mandados_busca_infrator_cumpridos'] }}</td>
                <td style="text-align: center; font-weight: bold; color: #dc3545;">{{ $estatisticas['mandados_busca_infrator_nao_cumpridos'] }}</td>
            </tr>
            <tr style="background-color: #ddd;">
                <td><strong>TOTAL</strong></td>
                <td style="text-align: center; font-weight: bold; color: #333;">{{ $operacao->total_mandados }}</td>
                <td style="text-align: center; font-weight: bold; color: #155724;">{{ $estatisticas['total_mandados_cumpridos'] }}</td>
                <td style="text-align: center; font-weight: bold; color: #721c24;">{{ $estatisticas['total_mandados_nao_cumpridos'] }}</td>
            </tr>
        </table>

        <div class="destaque-sucesso" style="margin-top: 15px;">
            <div style="text-align: center;">
                <strong>TAXA DE ÊXITO DA OPERAÇÃO: {{ $estatisticas['taxa_exito'] }}%</strong>
            </div>
        </div>
    </div>

    @if($resultado->mandados_prisao_cumpridos_detalhes && strtoupper(trim($resultado->mandados_prisao_cumpridos_detalhes)) !== 'N/A')
    <div class="secao">
        <div class="secao-titulo">DETALHES DOS PRESOS</div>
        
        <div class="destaque">
            <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px;">{{ $resultado->mandados_prisao_cumpridos_detalhes }}</pre>
        </div>
    </div>
    @endif

    <div class="secao">
        <div class="secao-titulo">PRISÕES EM FLAGRANTE</div>
        
        <div class="destaque-info" style="text-align: center; padding: 20px;">
            <div style="font-size: 32px; font-weight: bold; color: #0c5460;">
                {{ $estatisticas['prisoes_flagrante'] }}
            </div>
            <div style="font-size: 12px; margin-top: 5px;">
                Prisões realizadas em flagrante delito
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">ARMAS E MUNIÇÕES APREENDIDAS</div>
        
        <div class="campo">
            <span class="campo-label">Quantidade de Armas Apreendidas:</span>
            <span class="campo-valor" style="font-size: 14px; font-weight: bold;">{{ $resultado->quantidade_armas_apreendidas }}</span>
        </div>

        @if($resultado->tipo_arma_apreendida)
        <div class="campo">
            <span class="campo-label">Tipos de Armas:</span>
            <span class="campo-valor">
                @if(is_array($resultado->tipo_arma_apreendida))
                    {{ implode(', ', $resultado->tipo_arma_apreendida) }}
                @else
                    {{ $resultado->tipo_arma_apreendida }}
                @endif
            </span>
        </div>
        @endif

        @if($resultado->detalhes_armas_apreendidas && strtoupper(trim($resultado->detalhes_armas_apreendidas)) !== 'N/A')
        <div class="campo">
            <span class="campo-label">Detalhes das Armas:</span>
            <div class="destaque">
                <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px;">{{ $resultado->detalhes_armas_apreendidas }}</pre>
            </div>
        </div>
        @endif

        @if($resultado->municoes_apreendidas && strtoupper(trim($resultado->municoes_apreendidas)) !== 'N/A')
        <div class="campo">
            <span class="campo-label">Munições Apreendidas:</span>
            <div class="destaque">
                <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px;">{{ $resultado->municoes_apreendidas }}</pre>
            </div>
        </div>
        @endif
    </div>

    <div class="secao">
        <div class="secao-titulo">ENTORPECENTES APREENDIDOS</div>
        
        <div class="campo">
            <span class="campo-label">Tipos:</span>
            <span class="campo-valor" style="font-size: 13px; font-weight: bold;">
                @if(is_array($resultado->entorpecente_apreendido))
                    {{ implode(', ', $resultado->entorpecente_apreendido) }}
                @else
                    {{ $resultado->entorpecente_apreendido }}
                @endif
            </span>
        </div>

        @if($resultado->detalhes_entorpecentes && is_array($resultado->entorpecente_apreendido) && !in_array('NENHUM', $resultado->entorpecente_apreendido))
        <div class="campo">
            <span class="campo-label">Detalhes (Peso/Quantidade):</span>
            <div class="destaque">
                <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px;">{{ $resultado->detalhes_entorpecentes }}</pre>
            </div>
        </div>
        @endif
    </div>

    <div class="secao">
        <div class="secao-titulo">VALORES E OBJETOS APREENDIDOS</div>
        
        <div class="destaque-sucesso" style="margin-bottom: 15px;">
            <div class="campo">
                <span class="campo-label">Valores em Dinheiro:</span>
                <span class="campo-valor" style="font-size: 16px; font-weight: bold; color: #155724;">
                    R$ {{ number_format($resultado->valores_dinheiro, 2, ',', '.') }}
                </span>
            </div>
        </div>

        @if($resultado->veiculos_apreendidos)
        <div class="campo">
            <span class="campo-label">Veículos Apreendidos:</span>
            <div class="destaque">
                <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px;">{{ $resultado->veiculos_apreendidos }}</pre>
            </div>
        </div>
        @endif

        @if($resultado->demais_objetos_apreendidos)
        <div class="campo">
            <span class="campo-label">Demais Objetos Apreendidos:</span>
            <div class="destaque">
                <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px;">{{ $resultado->demais_objetos_apreendidos }}</pre>
            </div>
        </div>
        @endif
    </div>

    @if($resultado->outras_informacoes)
    <div class="secao">
        <div class="secao-titulo">OUTRAS INFORMAÇÕES</div>
        
        <div class="destaque">
            <pre style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 11px;">{{ $resultado->outras_informacoes }}</pre>
        </div>
    </div>
    @endif

    <div class="assinatura">
        <div style="display: table; width: 100%; margin-top: 50px;">
            <div style="display: table-cell; width: 50%; text-align: center; padding: 0 20px;">
                <p>_____________________________________</p>
                <p style="margin-top: 5px; font-weight: bold;">{{ $resultado->autoridade_responsavel_nome }}</p>
                <p>Autoridade Policial Responsável</p>
                <p style="margin-top: 3px; font-size: 10px;">Matrícula: {{ $resultado->autoridade_responsavel_matricula }}</p>
            </div>
            <div style="display: table-cell; width: 50%; text-align: center; padding: 0 20px;">
                <p>_____________________________________</p>
                <p style="margin-top: 5px; font-weight: bold;">{{ $resultado->policial_responsavel_nome }}</p>
                <p>Policial Civil Responsável pelo Preenchimento</p>
                <p style="margin-top: 3px; font-size: 10px;">Matrícula: {{ $resultado->policial_responsavel_matricula }}</p>
            </div>
        </div>
    </div>

    <div class="rodape">
        <p><strong>Polícia Civil da Paraíba</strong></p>
        <p>Documento gerado eletronicamente em {{ now()->format('d/m/Y') }} às {{ now()->format('H:i:s') }}</p>
        <p>ID da Operação: #{{ $operacao->id }} | ID do Resultado: #{{ $resultado->id }}</p>
    </div>
</body>
</html>