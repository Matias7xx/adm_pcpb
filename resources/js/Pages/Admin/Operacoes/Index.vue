<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
  mdiClipboardText,
  mdiMagnify,
  mdiEye,
  mdiFilterOffOutline,
  mdiAlertCircleOutline,
  mdiCheckCircle,
} from '@mdi/js';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseButton from '@/Components/BaseButton.vue';
import BaseIcon from '@/Components/BaseIcon.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import Pagination from '@/Components/Admin/Pagination.vue';

const props = defineProps({
  operacoes: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  origens: { type: Object, default: () => ({}) },
  ufs: { type: Object, default: () => ({}) },
});

const filtros = ref({
  busca: props.filters.busca ?? '',
  origem: props.filters.origem ?? '',
  data_inicio: props.filters.data_inicio ?? '',
  apoio_diop: props.filters.apoio_diop ?? '',
  uf_alvo: props.filters.uf_alvo ?? '',
});

const origensOptions = computed(() => ({
  '': 'Todas as origens',
  ...props.origens,
}));

function aplicarFiltros() {
  const params = {};
  Object.entries(filtros.value).forEach(([k, v]) => {
    if (v && v !== '') params[k] = v;
  });
  router.get(route('admin.operacoes-admin.index'), params, {
    preserveState: true,
  });
}

function limparFiltros() {
  filtros.value = {
    busca: '',
    origem: '',
    data_inicio: '',
    apoio_diop: '',
    uf_alvo: '',
  };
  router.get(route('admin.operacoes-admin.index'), {}, { preserveState: false });
}

function temFiltroAtivo() {
  return Object.values(filtros.value).some(v => v && v !== '');
}

function formatarData(data) {
  if (!data) return '-';
  return new Date(data).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
}

function origemClass(origem) {
  const map = {
    Nacional: 'bg-blue-100 text-blue-800',
    Estadual: 'bg-green-100 text-green-800',
    'Apoio a outro Estado': 'bg-yellow-100 text-yellow-800',
    'Alvo em outro Estado': 'bg-orange-100 text-orange-800',
  };
  return map[origem] ?? 'bg-gray-100 text-gray-700';
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Operações" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiClipboardText"
        title="Operações"
        main
      />

      <!-- Filtros -->
      <CardBox class="mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <FormField label="Busca">
            <FormControl
              v-model="filtros.busca"
              :icon="mdiMagnify"
              placeholder="Nome, unidade, cidade..."
            />
          </FormField>

          <FormField label="Origem">
            <FormControl
              v-model="filtros.origem"
              type="select"
              :options="origensOptions"
            />
          </FormField>

          <FormField label="Data da Operação">
            <FormControl v-model="filtros.data_inicio" type="date" />
          </FormField>

          <FormField label="Apoio DIOP">
            <FormControl
              v-model="filtros.apoio_diop"
              type="select"
              :options="{ '': 'Todos', '1': 'Com solicitação de apoio' }"
            />
          </FormField>

          <FormField
            v-if="filtros.origem === 'Alvo em outro Estado'"
            label="UF do Alvo"
          >
            <FormControl
              v-model="filtros.uf_alvo"
              type="select"
              :options="{ '': 'Todos os estados', ...ufs }"
            />
          </FormField>
        </div>

        <div class="flex gap-3 mt-4">
          <BaseButton
            label="Aplicar filtros"
            color="info"
            :icon="mdiMagnify"
            @click="aplicarFiltros"
          />
          <BaseButton
            v-if="temFiltroAtivo()"
            label="Limpar filtros"
            color="warning"
            :icon="mdiFilterOffOutline"
            @click="limparFiltros"
          />
        </div>
      </CardBox>

      <!-- Tabela -->
      <CardBox has-table>
        <div
          v-if="operacoes.data.length === 0"
          class="py-16 text-center text-gray-500"
        >
          <BaseIcon
            :path="mdiClipboardText"
            size="48"
            class="mx-auto mb-3 text-gray-300"
          />
          <p class="text-lg font-medium">Nenhuma operação encontrada</p>
          <p class="text-sm mt-1">Tente ajustar os filtros de busca.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr
                class="border-b border-gray-200 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
              >
                <th class="pb-3 pr-4">Operação</th>
                <th class="pb-3 pr-4">Data</th>
                <th class="pb-3 pr-4">Unidade</th>
                <th class="pb-3 pr-4">Origem</th>
                <th class="pb-3 pr-4">Debriefing</th>
                <th class="pb-3 pr-2 text-right">Ver</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="op in operacoes.data"
                :key="op.id"
                class="border-b border-gray-100 hover:bg-gray-50 transition-colors"
              >
                <!-- Nome + tag DIOP -->
                <td class="py-3 pr-4">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-gray-800">
                      {{ op.nome_operacao }}
                    </span>
                    <span
                      v-if="op.solicitacao_apoio_diop"
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300"
                      title="Esta operação possui solicitação de apoio à DIOP"
                    >
                      <BaseIcon :path="mdiAlertCircleOutline" size="12" />
                      Apoio DIOP
                    </span>
                  </div>
                  <div class="text-xs text-gray-400 mt-0.5">#{{ op.id }}</div>
                </td>

                <!-- Data -->
                <td class="py-3 pr-4 whitespace-nowrap text-gray-600 text-xs">
                  {{ formatarData(op.data_operacao) }}
                </td>

                <!-- Unidade -->
                <td class="py-3 pr-4 text-xs text-gray-700">
                  {{ op.unidade_policial_responsavel }}
                </td>

                <!-- Origem -->
                <td class="py-3 pr-4">
                  <span
                    class="inline-block px-2 py-0.5 rounded text-xs font-medium"
                    :class="origemClass(op.origem_operacao)"
                  >
                    {{ op.origem_operacao }}
                  </span>
                </td>

                <!-- Resultado -->
                <td class="py-3 pr-4">
                  <span
                    v-if="op.resultado"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                  >
                    <BaseIcon :path="mdiCheckCircle" size="12" />
                    Registrado
                  </span>
                  <span
                    v-else
                    class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500"
                  >
                    Pendente
                  </span>
                </td>

                <!-- Ação -->
                <td class="py-3 pr-2 text-right">
                  <Link
                    :href="route('admin.operacoes-admin.show', op.id)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-blue-100 text-blue-600 transition-colors"
                    title="Ver detalhes"
                  >
                    <BaseIcon :path="mdiEye" size="16" />
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="py-4 px-2">
          <Pagination :data="operacoes" />
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
