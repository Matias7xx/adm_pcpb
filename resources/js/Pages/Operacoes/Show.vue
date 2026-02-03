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
            <Link
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
            </Link>
            <a
              :href="route('operacoes.pdf', operacao.id)"
              target="_blank"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
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
              Baixar Relatório
            </a>
          </div>
        </div>

        <!-- Badge de origem -->
        <div class="flex items-center gap-3">
          <span
            :class="getOrigemClass(operacao.origem_operacao)"
            class="px-3 py-1 text-sm font-semibold rounded-full"
          >
            {{ operacao.origem_operacao }}
          </span>
          <span class="text-sm text-gray-600">
            Data da Operação: {{ formatarData(operacao.data_operacao) }}
          </span>
        </div>
      </div>

      <!-- Cards de Estatísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-600 mb-1">Total de Alvos</div>
          <div class="text-3xl font-bold text-[#bea55a]">
            {{ estatisticas.total_alvos }}
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-600 mb-1">Total de Mandados</div>
          <div class="text-3xl font-bold text-[#bea55a]">
            {{ estatisticas.total_mandados }}
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-600 mb-1">Policiais Empregados</div>
          <div class="text-3xl font-bold text-[#bea55a]">
            {{ estatisticas.policiais_empregados }}
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-600 mb-1">Viaturas</div>
          <div class="text-3xl font-bold text-[#bea55a]">
            {{ estatisticas.viaturas_empregadas }}
          </div>
        </div>
      </div>

      <!-- Grid de Informações -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Identificação -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Identificação da Operação
          </h2>

          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-600">Unidade Policial Responsável</label>
              <p class="text-gray-900 mt-1">{{ operacao.unidade_policial_responsavel }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">UF Responsável</label>
              <p class="text-gray-900 mt-1">{{ operacao.uf_responsavel }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Data da Operação</label>
              <p class="text-gray-900 mt-1">{{ formatarData(operacao.data_operacao) }}</p>
            </div>
          </div>
        </div>

        <!-- Responsáveis -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Responsáveis
          </h2>

          <div class="space-y-4">
            <div class="bg-gray-100 p-3 rounded">
              <label class="text-sm font-medium text-gray-800">Autoridade Policial Responsável</label>
              <p class="text-gray-900 font-semibold mt-1">
                {{ operacao.autoridade_responsavel_nome }}
              </p>
              <p class="text-sm text-gray-700">
                Matrícula: {{ operacao.autoridade_responsavel_matricula }}
              </p>
            </div>

            <div class="bg-gray-50 p-3 rounded">
              <label class="text-sm font-medium text-gray-600">Preenchido por</label>
              <p class="text-gray-900 font-semibold mt-1">
                {{ operacao.policial_responsavel_nome }}
              </p>
              <p class="text-sm text-gray-700">
                Matrícula: {{ operacao.policial_responsavel_matricula }}
              </p>
            </div>
          </div>
        </div>

        <!-- Briefing -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Briefing
          </h2>

          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-600">Local</label>
              <p class="text-gray-900 mt-1">{{ operacao.local_briefing }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Horário</label>
              <p class="text-gray-900 mt-1 text-lg font-semibold">
                {{ formatarHora(operacao.horario_briefing) || 'Não informado' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Mandados -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Mandados e Alvos
          </h2>

          <div class="space-y-2">
            <div class="flex justify-between items-center py-2 border-b">
              <span class="text-sm text-gray-600">Mandados de Prisão</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.mandados_prisao }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b">
              <span class="text-sm text-gray-600">Mandados de Busca e Apreensão</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.mandados_busca }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b">
              <span class="text-sm text-gray-600">Mandados Busca e Apreensão (Infrator)</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.mandados_busca_infrator }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b">
              <span class="text-sm text-gray-600">Alvos em Outros Estados</span>
              <span class="font-semibold text-gray-900">{{ estatisticas.alvos_outros_estados }}</span>
            </div>
            <div class="flex justify-between items-center py-2 bg-gray-100 px-2 rounded">
              <span class="text-sm font-semibold text-gray-900">TOTAL DE MANDADOS</span>
              <span class="font-bold text-xl text-gray-900">{{ estatisticas.total_mandados }}</span>
            </div>
          </div>
        </div>

        <!-- Localização e Crimes -->
        <div class="bg-white shadow rounded-lg p-6 lg:col-span-2">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
            Localização e Crimes Investigados
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Cidade(s) Alvo</label>
              <p class="text-gray-900 mt-2 whitespace-pre-line">{{ operacao.cidades_alvo }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-600">Crime(s) Investigado(s)</label>
              <p class="text-gray-900 mt-2 whitespace-pre-line">{{ operacao.crimes_investigados }}</p>
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
            <!-- <span v-if="operacao.updated_at !== operacao.created_at">
              | Última atualização em {{ formatarData(operacao.updated_at) }}
            </span> -->
          </p>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<style scoped>
</style>