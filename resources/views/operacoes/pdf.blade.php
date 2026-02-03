<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório da Operação - {{ $operacao->nome_operacao }}</title>
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
            color: #000; /* Cor padrão preta */
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }

        /* Estilo para o Brasão */
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
            background-color: #000; /* Fundo preto */
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
            background-color: #333; /* Cinza escuro */
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
            background-color: #f2f2f2; /* Cinza claro*/
        }

        .destaque {
            background-color: #fff;
            border: 1px solid #000; /* Borda preta */
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
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/brasao.png') }}" class="brasao" alt="Brasão">
        
        <h1>RELATÓRIO DE OPERAÇÃO POLICIAL</h1>
        <p style="margin-top: 10px; color: #333;">
            Gerado em: {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </div>

    <div class="secao">
        <div class="secao-titulo">IDENTIFICAÇÃO DA OPERAÇÃO</div>
        
        <div class="destaque">
            <div class="campo">
                <span class="campo-label">Nome da Operação:</span>
                <span class="campo-valor">{{ $operacao->nome_operacao }}</span>
            </div>
        </div>

        <div class="grid-2col">
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Unidade Policial Responsável:</span>
                    <span class="campo-valor">{{ $operacao->unidade_policial_responsavel }}</span>
                </div>
            </div>
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Data da Operação:</span>
                    <span class="campo-valor">{{ $operacao->data_operacao_formatada }}</span>
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
    </div>

    <div class="secao">
        <div class="secao-titulo">RESPONSÁVEIS</div>
        
        <div class="campo">
            <span class="campo-label">Autoridade Policial Responsável:</span>
            <span class="campo-valor">{{ $operacao->autoridade_responsavel_nome }}</span>
        </div>
        <div class="campo">
            <span class="campo-label">Matrícula:</span>
            <span class="campo-valor">{{ $operacao->autoridade_responsavel_matricula }}</span>
        </div>

        <div style="margin-top: 15px;">
            <div class="campo">
                <span class="campo-label">Policial Responsável pelo Preenchimento:</span>
                <span class="campo-valor">{{ $operacao->policial_responsavel_nome }}</span>
            </div>
            <div class="campo">
                <span class="campo-label">Matrícula:</span>
                <span class="campo-valor">{{ $operacao->policial_responsavel_matricula }}</span>
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">BRIEFING</div>
        
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
                    <span class="campo-valor">{{ $operacao->horario_briefing_formatado }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">MANDADOS E ALVOS</div>
        
        <table class="tabela">
            <tr>
                <th>Descrição</th>
                <th style="text-align: center; width: 100px;">Quantidade</th>
            </tr>
            <tr>
                <td>Quantidade Total de Alvos (Buscas e Prisões)</td>
                <td style="text-align: center; font-weight: bold;">{{ $estatisticas['total_alvos'] }}</td>
            </tr>
            <tr>
                <td>Mandados de Prisão</td>
                <td style="text-align: center;">{{ $estatisticas['mandados_prisao'] }}</td>
            </tr>
            <tr>
                <td>Mandados de Busca e Apreensão</td>
                <td style="text-align: center;">{{ $estatisticas['mandados_busca'] }}</td>
            </tr>
            <tr>
                <td>Mandados de Busca e Apreensão de Infrator</td>
                <td style="text-align: center;">{{ $estatisticas['mandados_busca_infrator'] }}</td>
            </tr>
            <tr>
                <td><strong>Total de Mandados</strong></td>
                <td style="text-align: center; font-weight: bold; background-color: #ddd;">{{ $estatisticas['total_mandados'] }}</td>
            </tr>
            <tr>
                <td>Alvos a serem cumpridos em outros estados</td>
                <td style="text-align: center;">{{ $estatisticas['alvos_outros_estados'] }}</td>
            </tr>
        </table>
    </div>

    <div class="secao">
        <div class="secao-titulo">RECURSOS EMPREGADOS</div>
        
        <div class="grid-2col">
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Policiais Civis Empregados:</span>
                    <span class="campo-valor">{{ $estatisticas['policiais_empregados'] }}</span>
                </div>
            </div>
            <div class="grid-col">
                <div class="campo">
                    <span class="campo-label">Viaturas da Polícia Civil:</span>
                    <span class="campo-valor">{{ $estatisticas['viaturas_empregadas'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">LOCALIZAÇÃO E CRIMES INVESTIGADOS</div>
        
        <div class="campo">
            <span class="campo-label">Cidade(s) Alvo da Operação:</span>
            <span class="campo-valor">{{ $operacao->cidades_alvo }}</span>
        </div>
        
        <div class="campo">
            <span class="campo-label">Crime(s) Investigado(s):</span>
            <span class="campo-valor">{{ $operacao->crimes_investigados }}</span>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">VINCULAÇÕES INSTITUCIONAIS</div>
        
        <div class="campo">
            <span class="campo-label">Vinculada à Unidade:</span>
            <span class="campo-valor">{{ $operacao->vinculada_unidade }}</span>
        </div>
        
        <div class="campo">
            <span class="campo-label">Vinculada à Unidade Especializada:</span>
            <span class="campo-valor">
                @if($operacao->vinculada_unidade_especializada === 'OUTRA')
                    OUTRA - {{ $operacao->outra_unidade_policial }}
                @else
                    {{ \App\Models\Operacao::getUnidadesEspecializadas()[$operacao->vinculada_unidade_especializada] ?? $operacao->vinculada_unidade_especializada }}
                @endif
            </span>
        </div>
        
        <div class="campo">
            <span class="campo-label">Vinculada à Delegacia Seccional:</span>
            <span class="campo-valor">{{ $operacao->vinculada_delegacia_seccional }}</span>
        </div>
    </div>

    @if($operacao->solicitacao_apoio_diop)
    <div class="secao">
        <div class="secao-titulo">SOLICITAÇÃO DE APOIO À DIOP</div>
        
        <div class="campo">
            <span class="campo-valor">{{ $operacao->solicitacao_apoio_diop }}</span>
        </div>
    </div>
    @endif

    <div class="assinatura">
        <p>_____________________________________</p>
        <p style="margin-top: 5px; font-weight: bold;">{{ $operacao->autoridade_responsavel_nome }}</p>
        <p>Autoridade Policial Responsável</p>
        <p style="margin-top: 5px; font-size: 10px;">Matrícula: {{ $operacao->autoridade_responsavel_matricula }}</p>
    </div>

    <div class="rodape">
        <p><strong>Polícia Civil da Paraíba</strong></p>
        <p>Documento gerado eletronicamente em {{ now()->format('d/m/Y') }} às {{ now()->format('H:i:s') }}</p>
        <p>ID da Operação: #{{ $operacao->id }}</p>
    </div>
</body>
</html>