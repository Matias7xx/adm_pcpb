<script setup>
import { ref } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
  operacoes: Object,
  filtros: Object,
});

// Filtros reativos
const filtrosBusca = ref({
  busca: props.filtros?.busca || '',
  data_inicio: props.filtros?.data_inicio || '',
  data_fim: props.filtros?.data_fim || '',
  origem: props.filtros?.origem || '',
});

const aplicarFiltros = () => {
  router.get(route('operacoes.index'), filtrosBusca.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const limparFiltros = () => {
  filtrosBusca.value = {
    busca: '',
    data_inicio: '',
    data_fim: '',
    origem: '',
  };
  router.get(route('operacoes.index'));
};

const excluirOperacao = id => {
  if (confirm('Tem certeza que deseja excluir esta operação?')) {
    router.delete(route('operacoes.destroy', id));
  }
};

const getOrigemClass = origem => {
  // Retorna uma cor neutra e profissional para todas as categorias
  return 'text-slate-700';
};
</script>

<template>
  <div>
    <Head>
      <title>Operações</title>
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
                Gerência de Operações
              </h1>
              <p class="text-gray-600 mt-1">
                Gerenciamento de operações da
                {{ $page.props.auth.user.lotacao }}
              </p>
            </div>
            <Link
              :href="route('operacoes.create')"
              class="px-6 py-3 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors flex items-center gap-2"
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
              Nova Operação
            </Link>
          </div>

          <!-- Filtros -->
          <div class="border-t pt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Buscar
                </label>
                <input
                  v-model.trim="filtrosBusca.busca"
                  type="text"
                  placeholder="Nome, autoridade, cidades..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  @keyup.enter="aplicarFiltros"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Data da Operação
                </label>
                <input
                  v-model="filtrosBusca.data_inicio"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Origem
                </label>
                <select
                  v-model="filtrosBusca.origem"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                >
                  <option value="">Todas</option>
                  <option value="Nacional">Nacional</option>
                  <option value="Estadual">Estadual</option>
                  <option value="Apoio a outro Estado">
                    Apoio a outro Estado
                  </option>
                </select>
              </div>
            </div>

            <div class="flex gap-3 mt-4">
              <button
                @click="aplicarFiltros"
                class="px-4 py-2 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors"
              >
                Aplicar Filtros
              </button>
              <button
                @click="limparFiltros"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
              >
                Limpar
              </button>
            </div>
          </div>
        </div>

        <!-- Lista de Operações -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div
            v-if="operacoes.data.length === 0"
            class="p-8 text-center text-gray-500"
          >
            <svg
              class="w-16 h-16 mx-auto mb-4 text-gray-300"
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
            <p class="text-lg">Nenhuma operação encontrada</p>
            <p class="text-sm mt-2">
              Clique em "Nova Operação" para cadastrar uma
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Operação
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Data
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Origem
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Autoridade
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    PDF
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="operacao in operacoes.data"
                  :key="operacao.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <Link
                        :href="route('operacoes.show', operacao.id)"
                        class="text-sm font-medium text-[#bea55a] hover:text-[#968143]"
                      >
                        {{ operacao.nome_operacao }}
                      </Link>
                      <span class="text-xs text-gray-500 mt-1">
                        ID: #{{ operacao.id }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{
                      new Date(operacao.data_operacao).toLocaleDateString(
                        'pt-BR'
                      )
                    }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="getOrigemClass(operacao.origem_operacao)"
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ operacao.origem_operacao }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    <div class="flex flex-col">
                      <span>{{ operacao.autoridade_responsavel_nome }}</span>
                      <span class="text-xs text-gray-500">
                        Matrícula:
                        {{ operacao.autoridade_responsavel_matricula }}
                      </span>
                    </div>
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                  >
                    <div class="flex items-center justify-end gap-2">
                      <!-- <Link
                        :href="route('operacoes.show', operacao.id)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Visualizar"
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
                      </Link> -->
                      <!-- <Link
                        :href="route('operacoes.edit', operacao.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                        title="Editar"
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
                      </Link> -->
                      <a
                        :href="route('operacoes.pdf', operacao.id)"
                        class="text-green-600 hover:text-green-900"
                        title="Baixar Relatório"
                        target="_blank"
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
                      </a>
                      <!-- <button
                        @click="excluirOperacao(operacao.id)"
                        class="text-red-600 hover:text-red-900"
                        title="Excluir"
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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          />
                        </svg>
                      </button> -->
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginação -->
          <div
            v-if="operacoes.last_page > 1"
            class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t"
          >
            <div class="text-sm text-gray-700">
              Mostrando
              <span class="font-medium">{{ operacoes.from }}</span>
              até
              <span class="font-medium">{{ operacoes.to }}</span>
              de
              <span class="font-medium">{{ operacoes.total }}</span>
              resultados
            </div>

            <div class="flex gap-2">
              <Link
                v-for="link in operacoes.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-2 rounded-lg text-sm',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : link.url
                      ? 'bg-white text-gray-700 hover:bg-gray-100'
                      : 'bg-gray-100 text-gray-400 cursor-not-allowed',
                ]"
                :disabled="!link.url"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<style scoped></style>
