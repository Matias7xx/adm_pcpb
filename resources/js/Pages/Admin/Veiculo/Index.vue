<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormControl from '@/Components/FormControl.vue'
import Pagination from '@/Components/Admin/Pagination.vue';
import Sort from '@/Components/Admin/Sort.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import { mdiPlus, mdiPencil, mdiTrashCan, mdiEye, mdiDownload, mdiToggleSwitch, mdiToggleSwitchOff, mdiMagnify, mdiClose } from '@mdi/js'

const props = defineProps({
  veiculos: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  can: {
    type: Object,
    default: () => ({}),
  },
})

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const tipo = ref(props.filters.tipo || '')
const isFiltering = ref(false)

// Debounce para o campo de busca
let searchTimeout = null
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filtrar()
  }, 500)
})

const filtrar = () => {
  isFiltering.value = true
  router.get(
    route('admin.veiculo.index'),
    {
      search: search.value,
      status: status.value,
      tipo: tipo.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        isFiltering.value = false
      }
    }
  )
}

const limparFiltros = () => {
  search.value = ''
  status.value = ''
  tipo.value = ''
  filtrar()
}

const removerFiltro = (filtro) => {
  if (filtro === 'search') search.value = ''
  if (filtro === 'status') status.value = ''
  if (filtro === 'tipo') tipo.value = ''
  filtrar()
}

const deletarVeiculo = (id) => {
  if (confirm('Tem certeza que deseja remover esta lista de veículos?')) {
    router.delete(route('admin.veiculo.destroy', id))
  }
}

const toggleAtivo = (id) => {
  router.patch(route('admin.veiculo.toggle-ativo', id), {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

const getStatusBadge = (veiculo) => {
  if (veiculo.status_display && veiculo.status_display.color) {
    const colorMap = {
      'slate': 'bg-slate-500',
      'blue': 'bg-blue-600',
      'red': 'bg-red-500',
      'yellow': 'bg-yellow-500',
      'green': 'bg-green-500'
    }
    return {
      color: colorMap[veiculo.status_display.color] || 'bg-gray-500',
      text: veiculo.status_display.text
    }
  } 
}

const getStatusLabel = (statusValue) => {
  const labels = {
    // 'validos': 'Válidos',
    'ativos': 'Ativos',
    'inativos': 'Inativos',
    // 'expirados': 'Expirados'
  }
  return labels[statusValue] || statusValue
}

const getTipoLabel = (tipoValue) => {
  const labels = {
    'pdf': 'PDF',
    'excel': 'Excel'
  }
  return labels[tipoValue] || tipoValue
}

const temFiltrosAtivos = computed(() => {
  return !!(search.value || status.value || tipo.value)
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Gerenciar Veículos Apreendidos" />

    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiPlus"
        title="Veículos Apreendidos"
        main
      >
        <BaseButton
          v-if="can.create"
          :route-name="route('admin.veiculo.create')"
          :icon="mdiPlus"
          label="Adicionar Lista"
          color="success"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <!-- Contador de Resultados e Status de Filtros -->
      <CardBox v-if="veiculos.total > 0" class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Exibindo <strong>{{ veiculos.from }}</strong> - <strong>{{ veiculos.to }}</strong> de <strong>{{ veiculos.total }}</strong> resultados
          </p>
          
          <div v-if="isFiltering" class="flex items-center gap-2 text-sm text-blue-600">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Filtrando...</span>
          </div>
        </div>
      </CardBox>

      <!-- Filtros Ativos (Badges) -->
      <CardBox v-if="temFiltrosAtivos" class="mb-4">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtros ativos:</span>
          
          <span v-if="search" class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
            <mdi-icon :path="mdiMagnify" size="14" />
            Busca: "{{ search }}"
            <button 
              @click="removerFiltro('search')" 
              class="hover:text-blue-900 dark:hover:text-blue-100"
              title="Remover filtro"
            >
              <mdi-icon :path="mdiClose" size="14" />
            </button>
          </span>

          <span v-if="status" class="inline-flex items-center gap-2 px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm">
            Status: {{ getStatusLabel(status) }}
            <button 
              @click="removerFiltro('status')" 
              class="hover:text-green-900 dark:hover:text-green-100"
              title="Remover filtro"
            >
              <mdi-icon :path="mdiClose" size="14" />
            </button>
          </span>

          <span v-if="tipo" class="inline-flex items-center gap-2 px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full text-sm">
            Tipo: {{ getTipoLabel(tipo) }}
            <button 
              @click="removerFiltro('tipo')" 
              class="hover:text-purple-900 dark:hover:text-purple-100"
              title="Remover filtro"
            >
              <mdi-icon :path="mdiClose" size="14" />
            </button>
          </span>

          <button
            @click="limparFiltros"
            class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium underline"
          >
            Limpar todos
          </button>
        </div>
      </CardBox>

      <!-- Filtros -->
      <CardBox class="mb-6">
        <form @submit.prevent="filtrar">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <FormControl
              v-model="search"
              type="text"
              placeholder="Buscar título ou descrição"
              :icon="mdiMagnify"
            />
            
            <select
              v-model="status"
              @change="filtrar"
              class="px-3 py-2 max-w-full focus:ring focus:outline-none border-gray-700 rounded w-full dark:placeholder-gray-400 bg-white dark:bg-slate-800 border"
            >
              <option value="">Todos os status</option>
              <!-- <option value="validos">Válidos</option> -->
              <option value="ativos">Ativos</option>
              <option value="inativos">Inativos</option>
              <!-- <option value="expirados">Expirados</option> -->
            </select>

            <select
              v-model="tipo"
              @change="filtrar"
              class="px-3 py-2 max-w-full focus:ring focus:outline-none border-gray-700 rounded w-full dark:placeholder-gray-400 bg-white dark:bg-slate-800 border"
            >
              <option value="">Todos os tipos</option>
              <option value="pdf">PDF</option>
              <option value="excel">Excel</option>
            </select>

            <div class="flex gap-2">
              <BaseButton
                label="Limpar"
                type="button"
                color="warning"
                outline
                class="flex-1"
                @click="limparFiltros"
                :disabled="!temFiltrosAtivos"
              />
            </div>
          </div>
        </form>
      </CardBox>

      <!-- Tabela de Veículos -->
      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th><Sort label="Título" attribute="titulo" /></th>
              <th>Tipo</th>
              <th>Tamanho</th>
              <th><Sort label="Publicação" attribute="data_publicacao" /></th>
              <th><Sort label="Expiração" attribute="data_expiracao" /></th>
              <th>Status</th>
              <th>Downloads</th>
              <th v-if="can.edit || can.delete">Ações</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="veiculos.data.length === 0">
              <td colspan="8" class="text-center py-8 text-gray-500">
                <div class="flex flex-col items-center gap-2">
                  <mdi-icon :path="mdiMagnify" size="48" class="text-gray-400" />
                  <p v-if="temFiltrosAtivos" class="font-medium">Nenhum resultado encontrado</p>
                  <p v-else>Nenhuma lista de veículos cadastrada</p>
                  <BaseButton
                    v-if="temFiltrosAtivos"
                    label="Limpar Filtros"
                    color="info"
                    outline
                    small
                    @click="limparFiltros"
                  />
                </div>
              </td>
            </tr>
            <tr v-for="veiculo in veiculos.data" :key="veiculo.id">
              <td data-label="Título">
                <div class="flex items-center space-x-3">
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                      {{ veiculo.titulo }}
                    </div>
                    <div
                      v-if="veiculo.descricao"
                      class="text-sm text-gray-500 line-clamp-1"
                    >
                      {{ veiculo.descricao }}
                    </div>
                  </div>
                </div>
              </td>

              <td data-label="Tipo">
                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                  {{ veiculo.tipo_arquivo.toUpperCase() }}
                </span>
              </td>

              <td data-label="Tamanho">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{ veiculo.tamanho_formatado }}
                </span>
              </td>

              <td data-label="Publicação">
                <span class="text-sm">
                  {{ new Date(veiculo.data_publicacao).toLocaleDateString('pt-BR') }}
                </span>
              </td>

              <td data-label="Expiração">
                <div class="flex flex-col">
                  <span class="text-sm">
                    {{ new Date(veiculo.data_expiracao).toLocaleDateString('pt-BR') }}
                  </span>
                  <span 
                    v-if="!veiculo.expirado && veiculo.dias_restantes <= 5"
                    class="text-xs text-orange-600 dark:text-orange-400"
                  >
                    {{ veiculo.dias_restantes }} {{ veiculo.dias_restantes === 1 ? 'dia' : 'dias' }} restantes
                  </span>
                </div>
              </td>

              <!-- CORRIGIDO: Status com fallback -->
              <td data-label="Status">
                <span
                  :class="[
                    'px-2 py-1 text-xs rounded-full text-white',
                    getStatusBadge(veiculo).color
                  ]"
                >
                  {{ getStatusBadge(veiculo).text }}
                </span>
              </td>

              <td data-label="Downloads">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-medium">{{ veiculo.downloads }}</span>
                  <mdi-icon :path="mdiDownload" size="16" class="text-gray-400" />
                </div>
              </td>

              <td v-if="can.edit || can.delete" class="before:hidden lg:w-1 whitespace-nowrap">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    v-if="can.edit"
                    :icon="veiculo.ativo ? mdiToggleSwitch : mdiToggleSwitchOff"
                    :color="veiculo.ativo ? 'success' : 'danger'"
                    small
                    @click="toggleAtivo(veiculo.id)"
                  />
                  <BaseButton
                    :icon="mdiEye"
                    :route-name="route('admin.veiculo.show', veiculo.id)"
                    color="info"
                    small
                  />
                  <BaseButton
                    v-if="can.edit"
                    :icon="mdiPencil"
                    :route-name="route('admin.veiculo.edit', veiculo.id)"
                    color="warning"
                    small
                  />
                  <BaseButton
                    v-if="can.delete"
                    :icon="mdiTrashCan"
                    color="danger"
                    small
                    @click="deletarVeiculo(veiculo.id)"
                  />
                </BaseButtons>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <BaseLevel>
            <Pagination :data="veiculos" />
          </BaseLevel>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>