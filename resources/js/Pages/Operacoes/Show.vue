<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
  operacao: Object,
  estatisticas: Object,
});

const formatarData = (data) => {
  return new Date(data).toLocaleDateString('pt-BR');
};

const formatarHora = (hora) => {
  if (!hora) return '';
  
  // Se já está no formato HH:MM, retorna direto
  if (typeof hora === 'string' && hora.match(/^\d{2}:\d{2}$/)) {
    return hora;
  }
  
  // Se tem mais caracteres (datetime), pega só os primeiros 5
  if (typeof hora === 'string' && hora.length >= 5) {
    return hora.substring(0, 5);
  }
  
  return hora;
};

const getOrigemClass = (origem) => {
  return 'bg-slate-100 text-slate-700 border border-slate-200';
};

const getUnidadeEspecializadaNome = (codigo) => {
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
    DECHRADI: 'DECHRADI (Homofóbicos, Racismo e Delitos de Intolerância Religiosa)',
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
  <Head>
    <title>{{ operacao.nome_operacao }}</title>
  </Head>

  <SiteNavbar />
  <Header />

  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <!-- Cabeçalho com ações -->
      <div class="bg-white shadow rounded-lg mb-6 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-4">
            <Link
              :href="route('operacoes.index')"
              class="text-gray-600 hover:text-gray-900"
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
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"
                />
              </svg>
            </Link>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">
                {{ operacao.nome_operacao }}
              </h1>
              <p class="text-gray-600 mt-1">ID: #{{ operacao.id }}</p>
            </div>
          </div>

          <div class="flex gap-3">
            <!-- <Link
              :href="route('operacoes.edit', operacao.id)"
              class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors flex items-center gap-2"
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
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                />
              </svg>
              Editar
            </Link> -->
            <a
              :href="route('operacoes.pdf', operacao.id)"
              target="_blank"
              class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2"
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
          </div>
        </div>

        <!-- Badge de status com origem da operação -->
        <div class="flex items-center gap-2 mt-4">
          <span
            :class="getOrigemClass(operacao.origem_operacao)"
            class="px-3 py-1 rounded-full text-sm font-medium"
          >
            {{ operacao.origem_operacao }}
          </span>
          <span class="text-gray-500">•</span>
          <span class="text-gray-600 text-sm">{{ formatarData(operacao.data_operacao) }}</span>
        </div>
      </div>

      <!-- Card de Resultado/Debriefing -->
      <div class="bg-gradient-to-r from-gray-200 to-gray-100 shadow rounded-lg mb-6 p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div>
              <h3 class="text-xl font-semibold text-gray-900">
                {{ operacao.resultado ? 'Resultado da Operação' : 'Resultado da Operação (Debriefing)' }}
              </h3>
              <p class="text-gray-600 text-sm mt-1">
                {{ operacao.resultado ? 'Os resultados desta operação já foram cadastrados' : 'Cadastre os resultados obtidos após a deflagração da operação' }}
              </p>
            </div>
          </div>
          
          <!-- Botão condicional -->
          <Link
            v-if="!operacao.resultado"
            :href="route('resultados-operacao.create', { operacao_id: operacao.id })"
            class="px-6 py-3 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors flex items-center gap-2 font-medium shadow-md hover:shadow-lg"
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
                d="M12 4v16m8-8H4"
              />
            </svg>
            Inserir Resultados/Debriefing
          </Link>

          <Link
            v-else
            :href="route('resultados-operacao.show', operacao.resultado.id)"
            class="px-6 py-3 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors flex items-center gap-2 font-medium shadow-md hover:shadow-lg"
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
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
              />
            </svg>
            Visualizar Resultados/Debriefing
          </Link>
        </div>
      </div>

      <!-- Grid com informações -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informações Básicas -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Informações da Operação
          </h2>

          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-600">Data da Operação</label>
              <p class="text-gray-900 mt-1">{{ formatarData(operacao.data_operacao) }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Origem da Operação</label>
              <p class="text-gray-900 mt-1">{{ operacao.origem_operacao }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">UF Responsável</label>
              <p class="text-gray-900 mt-1">{{ operacao.uf_responsavel }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Unidade Policial Responsável</label>
              <p class="text-gray-900 mt-1">{{ operacao.unidade_policial_responsavel }}</p>
            </div>
          </div>
        </div>

        <!-- Briefing -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Informações do Briefing
          </h2>

          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-600">Local do Briefing</label>
              <p class="text-gray-900 mt-1">{{ operacao.local_briefing }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Horário do Briefing</label>
              <p class="text-gray-900 mt-1">{{ formatarHora(operacao.horario_briefing) }}</p>
            </div>
          </div>
        </div>

        <!-- Responsáveis -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Responsáveis
          </h2>

          <div class="space-y-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Autoridade Policial Responsável</label>
              <p class="text-gray-900 mt-1">{{ operacao.autoridade_responsavel_nome }}</p>
              <p class="text-sm text-gray-600">Matrícula: {{ operacao.autoridade_responsavel_matricula }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Policial Responsável pelo Cadastro</label>
              <p class="text-gray-900 mt-1">{{ operacao.policial_responsavel_nome }}</p>
              <p class="text-sm text-gray-600">Matrícula: {{ operacao.policial_responsavel_matricula }}</p>
            </div>
          </div>
        </div>

        <!-- Estatísticas -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Estatísticas Planejadas
          </h2>

          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-100 p-4 rounded-lg">
              <p class="text-sm text-neutral-600 font-medium">Total de Alvos</p>
              <p class="text-2xl font-bold text-neutral-700 mt-1">{{ estatisticas.total_alvos }}</p>
            </div>

            <div class="bg-gray-100 p-4 rounded-lg">
              <p class="text-sm text-neutral-600 font-medium">Total de Mandados</p>
              <p class="text-2xl font-bold text-neutral-700 mt-1">{{ estatisticas.total_mandados }}</p>
            </div>

            <div class="bg-gray-100 p-4 rounded-lg">
              <p class="text-sm text-neutral-600 font-medium">Policiais</p>
              <p class="text-2xl font-bold text-neutral-700 mt-1">{{ estatisticas.policiais_empregados }}</p>
            </div>

            <div class="bg-gray-100 p-4 rounded-lg">
              <p class="text-sm text-neutral-600 font-medium">Viaturas</p>
              <p class="text-2xl font-bold text-neutral-700 mt-1">{{ estatisticas.viaturas_empregadas }}</p>
            </div>
          </div>
        </div>

        <!-- Mandados -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Distribuição de Mandados
          </h2>

          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-gray-700">Mandados de Prisão</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.mandados_prisao }}</span>
            </div>

            <div class="flex justify-between items-center">
              <span class="text-gray-700">Mandados de Busca e Apreensão</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.mandados_busca }}</span>
            </div>

            <div class="flex justify-between items-center">
              <span class="text-gray-700">Mandados de Busca de Infrator</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.mandados_busca_infrator }}</span>
            </div>

            <div class="flex justify-between items-center">
              <span class="text-gray-700">Alvos em Outros Estados</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.alvos_outros_estados }}</span>
            </div>
          </div>
        </div>

        <!-- Localização -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Localização e Crimes
          </h2>

          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-600">Cidades Alvo</label>
              <p class="text-gray-900 mt-1">{{ operacao.cidades_alvo }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Crimes Investigados</label>
              <p class="text-gray-900 mt-1">{{ operacao.crimes_investigados }}</p>
            </div>
          </div>
        </div>

        <!-- Vinculações -->
        <div class="bg-white shadow rounded-lg p-6 lg:col-span-2">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Vinculações Institucionais
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Vinculada à Unidade</label>
              <p class="text-gray-900 mt-2">{{ operacao.vinculada_unidade }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Unidade Especializada</label>
              <p class="text-gray-900 mt-2">
                {{ getUnidadeEspecializadaNome(operacao.vinculada_unidade_especializada) }}
                <span v-if="operacao.vinculada_unidade_especializada === 'OUTRA'" class="block text-md text-gray-900">
                  ({{ operacao.outra_unidade_policial }})
                </span>
              </p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Delegacia Seccional</label>
              <p class="text-gray-900 mt-2">{{ operacao.vinculada_delegacia_seccional }}</p>
            </div>
          </div>
        </div>

        <!-- Solicitação de Apoio -->
        <div v-if="operacao.solicitacao_apoio_diop" class="bg-white shadow rounded-lg p-6 lg:col-span-2">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Solicitação de Apoio à DIOP
          </h2>

          <div class="bg-gray-100 border border-gray-200 rounded-lg p-4">
            <p class="text-gray-900 whitespace-pre-line">{{ operacao.solicitacao_apoio_diop }}</p>
          </div>
        </div>
      </div>

      <!-- Rodapé com informações de cadastro -->
      <div class="bg-white shadow rounded-lg p-6 mt-6">
        <div class="text-sm text-gray-600 text-center">
          <p>
            Operação cadastrada em {{ formatarData(operacao.created_at) }}
          </p>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<style scoped>
</style>