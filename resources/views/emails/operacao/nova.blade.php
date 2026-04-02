@component('mail::message')
# Nova Operação Cadastrada

Uma nova operação foi registrada no sistema.

**Dados da Operação:**
- **Nome:** {{ $operacao->nome_operacao }}
- **Data:** {{ $operacao->data_operacao->format('d/m/Y') }}
- **Origem:** {{ $operacao->origem_operacao }}
- **Unidade Responsável:** {{ $operacao->unidade_policial_responsavel }}
- **UF Responsável:** {{ $operacao->uf_responsavel }}

**Responsáveis:**
- **Autoridade:** {{ $operacao->autoridade_responsavel_nome }} (Mat. {{ $operacao->autoridade_responsavel_matricula }})
- **Policial Responsável:** {{ $operacao->policial_responsavel_nome }} (Mat. {{ $operacao->policial_responsavel_matricula }})

**Estatísticas Planejadas:**
- **Total de Alvos:** {{ $operacao->quantidade_total_alvos }}
- **Mandados de Prisão:** {{ $operacao->quantidade_mandados_prisao }}
- **Mandados de Busca e Apreensão:** {{ $operacao->quantidade_mandados_busca_apreensao }}
- **Policiais Empregados:** {{ $operacao->quantidade_policiais_empregados }}
- **Viaturas Empregadas:** {{ $operacao->quantidade_viaturas_empregadas }}

@if($operacao->cidades_alvo)
**Cidades-Alvo:** {{ $operacao->cidades_alvo }}
@endif

@if($operacao->solicitacao_apoio_diop)
---
⚠️ **Esta operação contém solicitação de apoio à DIOP:**

{{ $operacao->solicitacao_apoio_diop }}
@endif

@component('mail::button', ['url' => $url])
Ver Operação no Painel
@endcomponent

@endcomponent
