<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
  resultado: Object,
  estatisticas: Object,
});

const formatarData = data => {
  if (!data) return '';
  return new Date(data).toLocaleDateString('pt-BR');
};

const formatarValor = valor => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(valor);
};

const excluirResultado = () => {
  if (
    confirm(
      'Tem certeza que deseja excluir este resultado? Esta ação não pode ser desfeita.'
    )
  ) {
    router.delete(route('resultados-operacao.destroy', resultado.id));
  }
};

const getUnidadeEspecializadaNome = codigo => {
  const unidades = {
    DAV: 'DAV (Acidente de Veículos)',
    DCCPAT: 'DCCPAT (Patrimônio)',
    DCCPES: 'DCCPES (Homicídios)',
    DDF: 'DDF (Defraudações)',
    DEAM: 'DEAM',
    DEATI: 'DEATI (Especializada de Atendimento ao Idoso)',
    DEATUR: 'DEATUR (Atendimento ao Turista)',
    DECC: 'DECC (Crimes Cibernéticos)',
    DECCOR: 'DECCOR (Combate a Corrupção)',
    DECCOT: 'DECCOT (Ordem Tributária)',
    DECHRADI:
      'DECHRADI (Homofóbicos, Racismo e Delitos de Intolerância Religiosa)',
    DECON: 'DECON (Consumidor)',
    DESARME: 'DESARME',
    DHE: 'DHE (Homicídios e Entorpecentes)',
    DMA: 'DMA (Meio Ambiente)',
    DRACO: 'DRACO',
    DRE: 'DRE (Entorpecentes)',
    DRFVC: 'DRFVC',
    GOE: 'GOE',
    GTE: 'GTE (Grupo Tático Especial)',
    DIJ: 'DIJ (Infância e Juventude)',
    DRCCIJ: 'DRCCIJ (Repressão Contra Crimes a Infância e Juventude)',
    DRF: 'DRF (Roubos e Furtos)',
    NH: 'NH (Núcleo de Homicídio)',
    NRQ: 'NRQ (Núcleo de Repressão Qualificada)',
    OUTRA: 'OUTRA',
  };
  return unidades[codigo] || codigo;
};
</script>

<template>
  <div>
    <Head>
      <title>
        Resultado da Operação - {{ resultado.operacao.nome_operacao }}
      </title>
    </Head>

    <Header />
    <SiteNavbar />

    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Cabeçalho -->
        <div class="bg-white shadow rounded-lg mb-6 p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">
                Resultado da Operação (Debriefing)
              </h1>
              <p class="text-xl text-gray-600 mt-2">
                {{ resultado.operacao.nome_operacao }}
              </p>
            </div>
            <div class="flex gap-2">
              <Link
                :href="route('resultados-operacao.edit', resultado.id)"
                class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-900 transition-colors flex items-center gap-2"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
                Editar
              </Link>
              <a
                :href="route('resultados-operacao.pdf', resultado.id)"
                target="_blank"
                class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors flex items-center gap-2"
              >
                <svg
                  class="w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                Baixar PDF
              </a>
              <Link
                :href="route('operacoes.show', resultado.operacao.id)"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Ver Operação
              </Link>
              <Link
                :href="route('operacoes.index')"
                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
              >
                Voltar
              </Link>
            </div>
          </div>

          <!-- Resumo Estatístico -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t">
            <div
              class="text-center bg-gray-100 p-4 rounded-lg border border-gray-300"
            >
              <p class="text-3xl font-bold text-gray-800">
                {{ estatisticas.total_prisoes }}
              </p>
              <p class="text-sm text-gray-600 mt-1">Prisões Totais</p>
            </div>
            <div
              class="text-center bg-gray-100 p-4 rounded-lg border border-gray-300"
            >
              <p class="text-3xl font-bold text-gray-800">
                {{ estatisticas.quantidade_armas }}
              </p>
              <p class="text-sm text-gray-600 mt-1">Armas Apreendidas</p>
            </div>
            <div
              class="text-center bg-gray-100 p-4 rounded-lg border border-gray-300"
            >
              <p class="text-3xl font-bold text-gray-800">
                {{ estatisticas.taxa_exito }}%
              </p>
              <p class="text-sm text-gray-600 mt-1">Taxa de Êxito</p>
            </div>
            <div
              class="text-center bg-gray-100 p-4 rounded-lg border border-gray-300"
            >
              <p class="text-3xl font-bold text-gray-800">
                {{ formatarValor(estatisticas.valores_dinheiro) }}
              </p>
              <p class="text-sm text-gray-600 mt-1">Valores Apreendidos</p>
            </div>
          </div>
        </div>

        <!-- Card 1: Identificação da Operação -->
        <div class="bg-white shadow rounded-lg p-6 mb-6 border border-gray-200">
          <h2
            class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2 flex items-center gap-2"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            Dados da Operação Cadastrada
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Nome da Operação</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.operacao.nome_operacao }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Data da Deflagração</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ formatarData(resultado.operacao.data_operacao) }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Unidade Responsável</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.unidade_policial_responsavel }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Policial Civil Responsável pelo preenchimento</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.policial_responsavel_nome }}
              </p>
              <p class="text-xs text-gray-600 mt-1">
                Mat: {{ resultado.policial_responsavel_matricula }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Autoridade Policial Responsável</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.autoridade_responsavel_nome }}
              </p>
              <p class="text-xs text-gray-600 mt-1">
                Mat: {{ resultado.autoridade_responsavel_matricula }}
              </p>
            </div>

            <div
              v-if="resultado.numero_processo_pje"
              class="bg-gray-50 p-3 rounded border border-gray-200"
            >
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Número do Processo PJE</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.numero_processo_pje }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Origem da Operação</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.operacao.origem_operacao }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >UF Responsável</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.operacao.uf_responsavel }}
              </p>
            </div>

            <!-- UFs do Alvo — só aparece quando origem = "Alvo em Outro Estado" -->
            <div
              v-if="
                resultado.operacao.origem_operacao === 'Alvo em outro Estado' &&
                resultado.operacao.ufs_alvo_outros_estados &&
                Object.keys(resultado.operacao.ufs_alvo_outros_estados).length
              "
              class="bg-blue-50 p-3 rounded border border-blue-200 md:col-span-2 lg:col-span-3"
            >
              <label class="text-xs font-semibold text-blue-700 uppercase"
                >Estado(s) do(s) Alvo(s)</label
              >
              <div class="flex flex-wrap gap-2 mt-2">
                <span
                  v-for="(qtd, uf) in resultado.operacao
                    .ufs_alvo_outros_estados"
                  :key="uf"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300"
                >
                  {{ uf }}
                  <span
                    class="bg-blue-200 text-blue-900 px-1.5 py-0.5 rounded-full text-xs"
                    >{{ qtd }} {{ qtd === 1 ? 'alvo' : 'alvos' }}</span
                  >
                </span>
              </div>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Vinculada à</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.operacao.vinculada_unidade }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Unidade Executora</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{
                  getUnidadeEspecializadaNome(
                    resultado.operacao.vinculada_unidade_especializada
                  )
                }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Delegacia Seccional</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.operacao.vinculada_delegacia_seccional }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Local Briefing</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.operacao.local_briefing }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Horário Briefing</label
              >
              <p class="text-gray-900 font-medium mt-1">
                {{ resultado.operacao.horario_briefing }}
              </p>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 md:col-span-2 lg:col-span-3"
            >
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Cidades Alvo</label
              >
              <p class="text-gray-900 mt-1 text-sm">
                {{ resultado.operacao.cidades_alvo }}
              </p>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 md:col-span-2 lg:col-span-3"
            >
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Crimes Investigados</label
              >
              <p class="text-gray-900 mt-1 text-sm">
                {{ resultado.operacao.crimes_investigados }}
              </p>
            </div>
          </div>
        </div>

        <!-- Card 2: Números da Operação -->
        <div class="bg-white shadow rounded-lg p-6 mb-6 border border-gray-200">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Números da Operação Planejada
          </h2>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 text-center"
            >
              <p class="text-2xl font-bold text-gray-800">
                {{ resultado.operacao.quantidade_total_alvos }}
              </p>
              <p class="text-xs text-gray-600 mt-1">Total de Alvos</p>
              <!-- Detalhamento por estado quando origem = Alvo em outro Estado -->
              <div
                v-if="
                  resultado.operacao.origem_operacao ===
                    'Alvo em outro Estado' &&
                  resultado.operacao.ufs_alvo_outros_estados &&
                  Object.keys(resultado.operacao.ufs_alvo_outros_estados).length
                "
                class="mt-2 space-y-1"
              >
                <div
                  v-for="(qtd, uf) in resultado.operacao
                    .ufs_alvo_outros_estados"
                  :key="uf"
                  class="flex justify-between text-xs px-1.5 py-0.5 bg-blue-50 rounded border border-blue-200"
                >
                  <span class="font-semibold text-blue-800">{{ uf }}</span>
                  <span class="text-blue-700">{{ qtd }}</span>
                </div>
              </div>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 text-center"
            >
              <p class="text-2xl font-bold text-gray-800">
                {{ resultado.operacao.quantidade_mandados_prisao }}
              </p>
              <p class="text-xs text-gray-600 mt-1">Mandados de Prisão</p>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 text-center"
            >
              <p class="text-2xl font-bold text-gray-800">
                {{ resultado.operacao.quantidade_mandados_busca_apreensao }}
              </p>
              <p class="text-xs text-gray-600 mt-1">Mandados de Busca</p>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 text-center"
            >
              <p class="text-2xl font-bold text-gray-800">
                {{
                  resultado.operacao
                    .quantidade_mandados_busca_apreensao_infrator
                }}
              </p>
              <p class="text-xs text-gray-600 mt-1">
                Mandados de Busca (Infrator)
              </p>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 text-center"
            >
              <p class="text-2xl font-bold text-gray-800">
                {{ resultado.operacao.quantidade_alvos_outros_estados }}
              </p>
              <p class="text-xs text-gray-600 mt-1">Alvos em Outros Estados</p>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 text-center"
            >
              <p class="text-2xl font-bold text-gray-800">
                {{ resultado.operacao.quantidade_policiais_empregados }}
              </p>
              <p class="text-xs text-gray-600 mt-1">Policiais Empregados</p>
            </div>

            <div
              class="bg-gray-50 p-3 rounded border border-gray-200 text-center"
            >
              <p class="text-2xl font-bold text-gray-800">
                {{ resultado.operacao.quantidade_viaturas_empregadas }}
              </p>
              <p class="text-xs text-gray-600 mt-1">Viaturas Empregadas</p>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t">
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Cidades Alvo</label
              >
              <p class="text-gray-900 mt-1">
                {{ resultado.operacao.cidades_alvo }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded border border-gray-200 mt-3">
              <label class="text-xs font-semibold text-gray-700 uppercase"
                >Crimes Investigados</label
              >
              <p class="text-gray-900 mt-1">
                {{ resultado.operacao.crimes_investigados }}
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Mandados e Taxa de Êxito -->
          <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Mandados e Taxa de Êxito
            </h2>

            <table class="w-full text-sm">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-2 text-gray-700">Tipo de Mandado</th>
                  <th class="text-center py-2 text-gray-700">Cumpridos</th>
                  <th class="text-center py-2 text-gray-700">Não Cumpridos</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr class="bg-gray-50">
                  <td class="py-2 text-sm text-gray-900">Mandados de Prisão</td>
                  <td
                    class="py-2 text-center text-sm font-semibold text-gray-800"
                  >
                    {{ estatisticas.mandados_prisao_cumpridos }}
                  </td>
                  <td
                    class="py-2 text-center text-sm font-semibold text-gray-600"
                  >
                    {{ estatisticas.mandados_prisao_nao_cumpridos }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-sm text-gray-900">Mandados de Busca</td>
                  <td
                    class="py-2 text-center text-sm font-semibold text-gray-800"
                  >
                    {{ estatisticas.mandados_busca_cumpridos }}
                  </td>
                  <td class="py-2 text-center text-sm text-gray-600">-</td>
                </tr>
                <tr class="bg-gray-50">
                  <td class="py-2 text-sm text-gray-900">
                    Mandados de Busca (Infrator)
                  </td>
                  <td
                    class="py-2 text-center text-sm font-semibold text-gray-800"
                  >
                    {{ estatisticas.mandados_busca_infrator_cumpridos }}
                  </td>
                  <td
                    class="py-2 text-center text-sm font-semibold text-gray-600"
                  >
                    {{ estatisticas.mandados_busca_infrator_nao_cumpridos }}
                  </td>
                </tr>
                <tr class="bg-gray-200 font-bold">
                  <td class="py-2 text-sm text-gray-900">TOTAL</td>
                  <td class="py-2 text-center text-sm font-bold text-gray-800">
                    {{ estatisticas.total_mandados_cumpridos }}
                  </td>
                  <td class="py-2 text-center text-sm font-bold text-gray-600">
                    {{ estatisticas.total_mandados_nao_cumpridos }}
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="mt-4 pt-4 border-t">
              <div
                class="flex justify-between items-center bg-gray-100 p-3 rounded border border-gray-300"
              >
                <span class="text-sm font-medium text-gray-700"
                  >Taxa de Êxito:</span
                >
                <span class="text-2xl font-bold text-gray-800"
                  >{{ estatisticas.taxa_exito }}%</span
                >
              </div>
            </div>
          </div>

          <!-- Detalhes dos Presos -->
          <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Detalhes dos Presos
            </h2>

            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
              <p class="text-gray-900 whitespace-pre-line text-sm">
                {{
                  resultado.mandados_prisao_cumpridos_detalhes ||
                  'Nenhum detalhe informado'
                }}
              </p>
            </div>
          </div>

          <!-- Prisões em Flagrante -->
          <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Prisões em Flagrante
            </h2>

            <div
              class="text-center py-6 bg-gray-100 rounded-lg border border-gray-300"
            >
              <p class="text-5xl font-bold text-gray-800">
                {{ estatisticas.prisoes_flagrante }}
              </p>
              <p class="text-sm text-gray-600 mt-2">
                Prisões realizadas em flagrante delito
              </p>
            </div>
          </div>

          <!-- Armas Apreendidas -->
          <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Armas Apreendidas
            </h2>

            <div class="space-y-3">
              <div class="bg-gray-100 p-4 rounded-lg border border-gray-300">
                <label class="text-sm font-medium text-gray-600"
                  >Quantidade</label
                >
                <p class="text-3xl font-bold text-gray-800 mt-1">
                  {{ resultado.quantidade_armas_apreendidas }}
                </p>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-600"
                  >Tipos de Armas</label
                >
                <div class="flex flex-wrap gap-2 mt-2">
                  <template
                    v-if="
                      Array.isArray(resultado.tipo_arma_apreendida) &&
                      resultado.tipo_arma_apreendida.length > 0
                    "
                  >
                    <span
                      v-for="tipo in resultado.tipo_arma_apreendida"
                      :key="tipo"
                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
                      :class="
                        tipo === 'NENHUMA' || tipo === 'PREJUDICADO'
                          ? 'bg-gray-100 text-gray-500 border border-gray-300'
                          : 'bg-red-100 text-red-800 border border-red-300'
                      "
                    >
                      {{ tipo }}
                    </span>
                  </template>
                  <span v-else class="text-gray-500 text-sm"
                    >Não informado</span
                  >
                </div>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-600"
                  >Detalhes</label
                >
                <div class="bg-gray-50 border border-gray-300 rounded p-3 mt-1">
                  <p class="text-gray-900 whitespace-pre-line text-sm">
                    {{
                      resultado.detalhes_armas_apreendidas ||
                      'Nenhum detalhe informado'
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Munições -->
          <div
            class="bg-white shadow rounded-lg p-6 lg:col-span-2 border border-gray-200"
          >
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Munições Apreendidas
            </h2>

            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
              <p class="text-gray-900 whitespace-pre-line text-sm">
                {{
                  resultado.municoes_apreendidas || 'Nenhuma munição apreendida'
                }}
              </p>
            </div>
          </div>

          <!-- Entorpecentes -->
          <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Entorpecentes Apreendidos
            </h2>

            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-600">Tipos</label>
                <div class="flex flex-wrap gap-2 mt-2">
                  <template
                    v-if="
                      Array.isArray(resultado.entorpecente_apreendido) &&
                      resultado.entorpecente_apreendido.length > 0
                    "
                  >
                    <span
                      v-for="tipo in resultado.entorpecente_apreendido"
                      :key="tipo"
                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
                      :class="
                        tipo === 'NENHUM'
                          ? 'bg-gray-100 text-gray-500 border border-gray-300'
                          : 'bg-orange-100 text-orange-800 border border-orange-300'
                      "
                    >
                      {{ tipo }}
                    </span>
                  </template>
                  <span v-else class="text-gray-500 text-sm"
                    >Não informado</span
                  >
                </div>
              </div>

              <div
                v-if="
                  Array.isArray(resultado.entorpecente_apreendido) &&
                  resultado.detalhes_entorpecentes &&
                  !resultado.entorpecente_apreendido.includes('NENHUM')
                "
              >
                <label class="text-sm font-medium text-gray-600"
                  >Detalhes (Peso/Quantidade)</label
                >
                <div
                  class="bg-gray-50 border border-gray-300 rounded-lg p-4 mt-2"
                >
                  <p class="text-gray-900 whitespace-pre-line text-sm">
                    {{ resultado.detalhes_entorpecentes }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Valores em Dinheiro -->
          <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Valores em Dinheiro
            </h2>

            <div
              class="text-center py-6 bg-gray-100 rounded-lg border border-gray-300"
            >
              <p class="text-4xl font-bold text-gray-800">
                {{ formatarValor(resultado.valores_dinheiro) }}
              </p>
              <p class="text-sm text-gray-600 mt-2">
                Total apreendido em espécie
              </p>
            </div>
          </div>

          <!-- Veículos -->
          <div
            class="bg-white shadow rounded-lg p-6 lg:col-span-2 border border-gray-200"
          >
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Veículos Apreendidos
            </h2>

            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
              <p class="text-gray-900 whitespace-pre-line text-sm">
                {{
                  resultado.veiculos_apreendidos || 'Nenhum veículo apreendido'
                }}
              </p>
            </div>
          </div>

          <!-- Demais Objetos -->
          <div
            class="bg-white shadow rounded-lg p-6 lg:col-span-2 border border-gray-200"
          >
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Demais Objetos Apreendidos
            </h2>

            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
              <p class="text-gray-900 whitespace-pre-line text-sm">
                {{
                  resultado.demais_objetos_apreendidos ||
                  'Nenhum objeto apreendido'
                }}
              </p>
            </div>
          </div>

          <!-- Outras Informações -->
          <div
            v-if="resultado.outras_informacoes"
            class="bg-white shadow rounded-lg p-6 lg:col-span-2 border border-gray-200"
          >
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
              Outras Informações
            </h2>

            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
              <p class="text-gray-900 whitespace-pre-line text-sm">
                {{ resultado.outras_informacoes }}
              </p>
            </div>
          </div>
        </div>

        <!-- Rodapé: data de cadastro + exclusão -->
        <div class="bg-white shadow rounded-lg p-6 mt-6 border border-gray-200">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">
              Resultado cadastrado em
              <span class="font-medium text-gray-700">{{
                formatarData(resultado.created_at)
              }}</span>
            </p>
            <!-- <button
              @click="excluirResultado"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2 text-sm"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
              Excluir Resultado
            </button> -->
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<style scoped></style>
