<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
  mdiNewspaper,
  mdiChartTimelineVariant,
  mdiReload,
  mdiEye,
  mdiStar,
  mdiPlus,
} from '@mdi/js';
import LineChart from '@/Components/Charts/LineChart.vue';
import SectionMain from '@/Components/SectionMain.vue';
import CardBox from '@/Components/CardBox.vue';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import BaseButton from '@/Components/BaseButton.vue';

const props = defineProps({
  dashboardData: Object,
  chartData: Object,
  resumoMeses: Array,
});

const chartDataReactive = ref(props.chartData);

const fillChartData = () => {
  chartDataReactive.value = props.chartData;
};

const formatarNumero = numero => {
  const n = Number(numero);
  if (isNaN(n)) return '0';
  return new Intl.NumberFormat('pt-BR').format(n);
};

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  scales: {
    x: {
      display: true,
      grid: { color: 'rgba(156,163,175,0.15)' },
      ticks: { color: '#9CA3AF' },
    },
    y: {
      type: 'linear',
      display: true,
      position: 'left',
      title: { display: true, text: 'Publicadas', color: '#3B82F6' },
      grid: { color: 'rgba(156,163,175,0.15)' },
      ticks: { color: '#3B82F6', stepSize: 1 },
    },
    y1: {
      type: 'linear',
      display: true,
      position: 'right',
      title: { display: true, text: 'Visualizações', color: '#10B981' },
      grid: { drawOnChartArea: false },
      ticks: { color: '#10B981' },
      min: 0,
    },
  },
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: { color: '#9CA3AF', usePointStyle: true, pointStyleWidth: 10 },
    },
    tooltip: { mode: 'index', intersect: false },
  },
};

const mesAtual = computed(() => {
  const mes = new Date().toLocaleDateString('pt-BR', {
    month: 'long',
    year: 'numeric',
  });
  return mes.charAt(0).toUpperCase() + mes.slice(1);
});
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Dashboard" />
    <SectionMain>
      <!-- Cabeçalho -->
      <div class="mb-8">
        <SectionTitleLineWithButton
          :icon="mdiChartTimelineVariant"
          title="Dashboard"
          main
        >
          <BaseButton
            :route-name="route('admin.noticias.create')"
            :icon="mdiPlus"
            label="Nova Notícia"
            color="info"
            rounded-full
            small
          />
        </SectionTitleLineWithButton>
      </div>

      <!-- Cards de Métricas Principais -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8 max-w-2xl">
        <!-- Publicadas -->
        <div
          class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 border-emerald-500"
        >
          <div class="flex items-center justify-between">
            <div>
              <p
                class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
              >
                Total de Notícias Publicadas
              </p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatarNumero(dashboardData.publicadas) }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ dashboardData.novas_mes }} em {{ mesAtual }}
              </p>
            </div>
            <div
              class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center"
            >
              <svg
                class="w-6 h-6 text-emerald-600 dark:text-emerald-400"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path :d="mdiStar" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Visualizações -->
        <div
          class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 border-purple-500"
        >
          <div class="flex items-center justify-between">
            <div>
              <p
                class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
              >
                Total de Visualizações
              </p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatarNumero(dashboardData.total_visualizacoes) }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                total acumulado
              </p>
            </div>
            <div
              class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center"
            >
              <svg
                class="w-6 h-6 text-purple-600 dark:text-purple-400"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path :d="mdiEye" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráfico + Mais Visualizada do Mês -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Gráfico (ocupa 2/3) -->
        <div class="xl:col-span-2">
          <CardBox
            title="Evolução nos Últimos 6 Meses"
            :icon="mdiChartTimelineVariant"
            :header-icon="mdiReload"
            class="shadow-lg h-full"
            @header-icon-click="fillChartData"
          >
            <div v-if="chartDataReactive" class="h-72">
              <LineChart
                :data="chartDataReactive"
                :options="chartOptions"
                class="h-full"
              />
            </div>
          </CardBox>
        </div>

        <!-- Resumo do mês (ocupa 1/3) -->
        <div>
          <CardBox
            title="Resumo do Mês"
            :icon="mdiNewspaper"
            class="shadow-lg h-full"
          >
            <div class="space-y-4">
              <div
                class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Publicadas em {{ mesAtual }}</span
                >
                <span
                  class="text-lg font-bold text-emerald-600 dark:text-emerald-400"
                  >{{ dashboardData.novas_mes }}</span
                >
              </div>

              <div
                class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Visualizações em {{ mesAtual }}</span
                >
                <span
                  class="text-lg font-bold text-purple-600 dark:text-purple-400"
                  >{{ formatarNumero(dashboardData.visualizacoes_mes) }}</span
                >
              </div>

              <!-- Mais visualizada do mês -->
              <div
                v-if="dashboardData.mais_visualizada_mes"
                class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800"
              >
                <p
                  class="text-xs font-medium text-purple-700 dark:text-purple-400 mb-1"
                >
                  Mais visualizada do mês
                </p>
                <p
                  class="text-sm font-semibold text-gray-800 dark:text-white line-clamp-2"
                >
                  {{ dashboardData.mais_visualizada_mes.titulo }}
                </p>
                <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                  {{
                    formatarNumero(
                      dashboardData.mais_visualizada_mes.visualizacoes
                    )
                  }}
                  visualizações
                </p>
              </div>
            </div>
          </CardBox>
        </div>
      </div>

      <!-- Histórico por Mês -->
      <div v-if="resumoMeses && resumoMeses.length" class="mb-8">
        <h3
          class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4"
        >
          Histórico dos Últimos Meses
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
          <div
            v-for="mes in resumoMeses"
            :key="mes.mes_curto"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition-shadow duration-200"
          >
            <p
              class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3"
            >
              {{ mes.mes_curto }}
            </p>
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-gray-400"
                  >Publicadas</span
                >
                <span
                  class="text-sm font-bold text-emerald-600 dark:text-emerald-400"
                  >{{ mes.publicadas }}</span
                >
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-gray-400"
                  >Views</span
                >
                <span
                  class="text-sm font-bold text-purple-600 dark:text-purple-400"
                  >{{ formatarNumero(mes.visualizacoes) }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Últimas Notícias Publicadas -->
      <CardBox
        title="Últimas Publicadas"
        :icon="mdiNewspaper"
        class="shadow-lg"
      >
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th
                  class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-400"
                >
                  Título
                </th>
                <th
                  class="text-center py-3 px-4 font-semibold text-gray-600 dark:text-gray-400"
                >
                  Destaque
                </th>
                <th
                  class="text-center py-3 px-4 font-semibold text-gray-600 dark:text-gray-400"
                >
                  Visualizações
                </th>
                <th
                  class="text-right py-3 px-4 font-semibold text-gray-600 dark:text-gray-400"
                >
                  Data
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="noticia in dashboardData.ultimas_publicadas"
                :key="noticia.id"
                class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors"
              >
                <td class="py-3 px-4">
                  <a
                    :href="route('admin.noticias.show', noticia.id)"
                    class="text-blue-600 dark:text-blue-400 hover:underline font-medium line-clamp-1"
                  >
                    {{ noticia.titulo }}
                  </a>
                </td>
                <td class="py-3 px-4 text-center">
                  <span
                    v-if="noticia.destaque"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
                  >
                    Destaque
                  </span>
                  <span v-else class="text-gray-400 text-xs">—</span>
                </td>
                <td
                  class="py-3 px-4 text-center text-gray-700 dark:text-gray-300"
                >
                  {{ formatarNumero(noticia.visualizacoes) }}
                </td>
                <td
                  class="py-3 px-4 text-right text-gray-500 dark:text-gray-400 text-xs"
                >
                  {{ noticia.data_publicacao }}
                </td>
              </tr>
            </tbody>
          </table>

          <div class="mt-4 text-right">
            <a
              :href="route('admin.noticias.index')"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              Ver todas as notícias →
            </a>
          </div>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
